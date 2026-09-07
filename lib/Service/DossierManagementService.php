<?php

/**
 * Dossier Management Service
 *
 * Index and detail aggregation for the dossier surface, plus the membership
 * operations behind it: create, rename (with bound-folder sync), link and
 * unlink documents, and lifecycle transitions.
 *
 * MEMBERSHIP IS A UNION, NOT A FOLDER. A dossier keeps a bound home folder
 * (`@self.folder`) as the physical home of the files it owns, but membership
 * is the deduplicated union of that folder's files and the explicit
 * `documents[]` references — so one document can belong to several dossiers
 * without being copied. A reference whose target is gone or unreadable is
 * surfaced as a visible marker and never silently dropped: a dossier that
 * quietly lists four of its five documents is worse than one that says the
 * fifth is missing.
 *
 * WRITES ARE FULL-PAYLOAD. OpenRegister saves are PUT-semantic, so every
 * update here carries the whole object forward. A rename that posted only
 * `name` would null `bases`, `checkedOn`, `status` and `documents`.
 *
 * @category  Service
 * @package   OCA\Filinq\Service
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link      https://www.filinq.app
 *
 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

declare(strict_types=1);

namespace OCA\Filinq\Service;

use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * Aggregates and mutates dossiers for the dossier-management surface.
 *
 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) The aggregation deliberately
 *     wires existing capabilities (bases, folder enumeration, batch state,
 *     anonymisation links) rather than reimplementing any of them.
 */
class DossierManagementService {

	/**
	 * The register every filinq schema lives in.
	 *
	 * @var string
	 */
	private const REGISTER = 'filinq';

	/**
	 * The dossier schema slug.
	 *
	 * @var string
	 */
	private const SCHEMA = 'dossier';

	/**
	 * The status a dossier without one is read as.
	 *
	 * `status` is optional and existing objects were deliberately not
	 * migrated, so absence is a value with a meaning, not missing data.
	 *
	 * @var string
	 */
	public const DEFAULT_STATUS = 'open';

	/**
	 * Memoised `base` vocabulary, slug => name.
	 *
	 * Read once per request: the index resolves grondslagen for every row, and
	 * re-reading the vocabulary per row turns one query into N.
	 *
	 * @var array<string, string>|null
	 */
	private ?array $baseLabels = null;

	/**
	 * The declared lifecycle, mirroring `x-openregister-lifecycle` on the schema.
	 *
	 * Mirrored, not owned: OpenRegister's guard is the authority and rejects an
	 * out-of-order write regardless of what this map says. It exists so the UI
	 * can offer only the legal targets instead of offering all five and letting
	 * the operator discover the rule by being refused.
	 *
	 * @var array<string, list<string>>
	 */
	private const TRANSITIONS = [
		'open' => ['in-review'],
		'in-review' => ['processed', 'open'],
		'processed' => ['published', 'closed'],
		'published' => ['closed'],
		'closed' => [],
	];

	/**
	 * Constructor.
	 *
	 * @param DossierObjectRepository $repository Dossier object + folder resolution.
	 * @param IRootFolder $rootFolder Nextcloud filesystem root.
	 * @param IUserSession $userSession The current session.
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct(
		private readonly DossierObjectRepository $repository,
		private readonly IRootFolder $rootFolder,
		private readonly IUserSession $userSession,
		private readonly LoggerInterface $logger,
	) {

	}//end __construct()

	/**
	 * Every dossier the caller can read, with its live context.
	 *
	 * @return array<int, array<string, mixed>> Index rows.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function index(): array {
		$objectService = $this->requireObjectService();

		$objects = $this->findAllOf(objectService: $objectService, schema: self::SCHEMA);

		$rows = [];
		foreach ($objects as $object) {
			$payload = $this->payloadOf(object: $object);
			$uuid = $this->uuidOf(object: $object, payload: $payload);
			if ($uuid === '') {
				continue;
			}

			$members = $this->members(object: $object, payload: $payload);

			$rows[] = [
				'id' => $uuid,
				'name' => (string)($payload['name'] ?? ''),
				'description' => (string)($payload['description'] ?? ''),
				'status' => $this->statusOf(payload: $payload),
				'checkedOn' => (string)($payload['checkedOn'] ?? ''),
				'documentCount' => count($members['documents']),
				'missingCount' => $members['missing'],
				'bases' => $this->resolveBases(payload: $payload),
			];
		}

		return $rows;

	}//end index()

	/**
	 * One dossier with its documents, grondslagen, batch runs and publication state.
	 *
	 * @param string $dossierId The dossier object UUID.
	 *
	 * @return array<string, mixed> The aggregated dossier.
	 *
	 * @throws RuntimeException When the caller cannot read the dossier.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function detail(string $dossierId): array {
		$object = $this->requireReadable(dossierId: $dossierId);
		$payload = $this->payloadOf(object: $object);
		$members = $this->members(object: $object, payload: $payload);
		$status = $this->statusOf(payload: $payload);

		return [
			'id' => $dossierId,
			'name' => (string)($payload['name'] ?? ''),
			'description' => (string)($payload['description'] ?? ''),
			'status' => $status,
			'availableTransitions' => (self::TRANSITIONS[$status] ?? []),
			'checkedOn' => (string)($payload['checkedOn'] ?? ''),
			'bases' => $this->resolveBases(payload: $payload),
			'documents' => $members['documents'],
			'batchRuns' => $this->batchRuns(object: $object, payload: $payload),
			'publication' => $this->publication(),
			'capabilities' => [
				// Presence gates. Each optional capability is HIDDEN when
				// absent, never offered-and-broken — a "mark as checked" action
				// that 404s is worse than no action at all.
				//
				// Both are in-app capabilities that have not shipped yet
				// (anonymization-review-workbench, woo-publicatie-pipeline), so
				// they are reported absent from the one place that decides it.
				// When either lands, flip it here and the detail picks it up.
				'reviewWorkbench' => false,
				'publicationPipeline' => $this->publication()['installed'],
			],
		];

	}//end detail()

	/**
	 * Create a dossier bound to a home folder.
	 *
	 * @param string $name The dossier name; also the home-folder name.
	 * @param string $description Optional free text.
	 * @param array<int, string> $bases Grondslag slugs.
	 *
	 * @return array<string, mixed> The created dossier's detail shape.
	 *
	 * @throws RuntimeException When the name is empty or the save fails.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function create(string $name, string $description = '', array $bases = []): array {
		$name = trim($name);
		if ($name === '') {
			throw new RuntimeException('A dossier needs a name.', 400);
		}

		$objectService = $this->requireObjectService();
		$folder = $this->createHomeFolder(name: $name);

		$saved = $objectService->saveObject(
			object: [
				'@self' => [
					'register' => self::REGISTER,
					'schema' => self::SCHEMA,
					'folder' => $folder->getId(),
				],
				'name' => $name,
				'description' => $description,
				'bases' => array_values($bases),
				'documents' => [],
				// Written explicitly, not left to the reader's default.
				// OpenRegister's lifecycle guard compares the STORED value, and
				// an absent status is `""` to it — from which no transition is
				// declared. A dossier created without this could never take its
				// first transition: the UI offered "Start review" and the save
				// came back 409 "No transition allows moving from ''".
				'status' => self::DEFAULT_STATUS,
			],
			register: self::REGISTER,
			schema: self::SCHEMA
		);

		$payload = $this->payloadOf(object: $saved);

		return $this->detail(dossierId: $this->uuidOf(object: $saved, payload: $payload));

	}//end create()

	/**
	 * Rename a dossier and keep its bound home folder in sync.
	 *
	 * The object rename ALWAYS succeeds; the folder rename is best-effort and
	 * reports rather than blocks. A caller without write permission, or a
	 * sibling already holding the target name, must not cost the operator
	 * their rename — and a name collision must never merge two folders.
	 *
	 * @param string $dossierId The dossier object UUID.
	 * @param string $name The new name.
	 *
	 * @return array<string, mixed> The detail shape plus a `folderWarning` key.
	 *
	 * @throws RuntimeException When the name is empty or the caller cannot read the dossier.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function rename(string $dossierId, string $name): array {
		$name = trim($name);
		if ($name === '') {
			throw new RuntimeException('A dossier needs a name.', 400);
		}

		$object = $this->requireReadable(dossierId: $dossierId);
		$payload = $this->payloadOf(object: $object);

		$warning = $this->renameHomeFolder(object: $object, payload: $payload, name: $name);
		$this->save(object: $object, payload: ($payload + []), changes: ['name' => $name]);

		$detail = $this->detail(dossierId: $dossierId);
		$detail['folderWarning'] = $warning;

		return $detail;

	}//end rename()

	/**
	 * Move a dossier to a new lifecycle status.
	 *
	 * @param string $dossierId The dossier object UUID.
	 * @param string $status The target status.
	 *
	 * @return array<string, mixed> The refreshed detail shape.
	 *
	 * @throws RuntimeException When the transition is not declared, or the save is refused.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function transition(string $dossierId, string $status): array {
		$object = $this->requireReadable(dossierId: $dossierId);
		$payload = $this->payloadOf(object: $object);
		$current = $this->statusOf(payload: $payload);

		if (in_array($status, (self::TRANSITIONS[$current] ?? []), true) === false) {
			// Refused here as well as by OpenRegister. The server-side guard is
			// the authority, but answering 409 with both states reads far
			// better than the generic rejection the guard produces.
			throw new RuntimeException(
				sprintf('A dossier cannot move from "%s" to "%s".', $current, $status),
				409
			);
		}

		// A dossier stored before `status` existed holds no value, and the
		// lifecycle guard reads that as `""` rather than as the initial state —
		// so every legacy dossier is frozen until the initial state is written
		// once. Settle it first, then transition. Both writes are full-payload.
		if (trim((string)($payload['status'] ?? '')) === '' && $status !== self::DEFAULT_STATUS) {
			$this->save(
				object: $object,
				payload: $payload,
				changes: ['status' => self::DEFAULT_STATUS]
			);
			$payload['status'] = self::DEFAULT_STATUS;
		}

		$this->save(object: $object, payload: $payload, changes: ['status' => $status]);

		return $this->detail(dossierId: $dossierId);

	}//end transition()

	/**
	 * Add an existing file to a dossier by reference, without moving it.
	 *
	 * Link semantics: the file stays where it is and the dossier gains a
	 * reference, so the same document can be a member of several dossiers.
	 *
	 * @param string $dossierId The dossier object UUID.
	 * @param int $fileId The Nextcloud file node id.
	 *
	 * @return array<string, mixed> The refreshed detail shape.
	 *
	 * @throws RuntimeException When the caller cannot read the dossier or the file.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function linkDocument(string $dossierId, int $fileId): array {
		$object = $this->requireReadable(dossierId: $dossierId);
		$payload = $this->payloadOf(object: $object);

		if ($this->nodeFor(fileId: $fileId) === null) {
			throw new RuntimeException('That file does not exist or you cannot read it.', 404);
		}

		$documents = $this->documentRefs(payload: $payload);
		if (in_array((string)$fileId, $documents, true) === false) {
			$documents[] = (string)$fileId;
			$this->save(object: $object, payload: $payload, changes: ['documents' => $documents]);
		}

		return $this->detail(dossierId: $dossierId);

	}//end linkDocument()

	/**
	 * Remove a document from a dossier.
	 *
	 * Two different operations behind one verb, and the caller is told which
	 * one it will be by {@see self::removalMode()} before confirming:
	 *
	 *  - The file lives in this dossier's home folder and no other dossier
	 *    references it → it is moved to the TRASHBIN, recoverable. Never a
	 *    hard delete.
	 *  - The file is a reference (lives elsewhere, or another dossier also
	 *    holds it) → only this dossier's membership reference is dropped and
	 *    the file is untouched.
	 *
	 * @param string $dossierId The dossier object UUID.
	 * @param int $fileId The Nextcloud file node id.
	 *
	 * @return array<string, mixed> The refreshed detail shape.
	 *
	 * @throws RuntimeException When the caller cannot read the dossier.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function removeDocument(string $dossierId, int $fileId): array {
		$object = $this->requireReadable(dossierId: $dossierId);
		$payload = $this->payloadOf(object: $object);

		$documents = $this->documentRefs(payload: $payload);
		$remaining = array_values(array_filter(
			$documents,
			static fn (string $ref): bool => $ref !== (string)$fileId
		));

		if ($remaining !== $documents) {
			$this->save(object: $object, payload: $payload, changes: ['documents' => $remaining]);
		}

		if ($this->removalMode(dossierId: $dossierId, fileId: $fileId) === 'trash') {
			$this->trash(fileId: $fileId);
		}

		return $this->detail(dossierId: $dossierId);

	}//end removeDocument()

	/**
	 * Whether removing this file would trash it or merely unlink it.
	 *
	 * Exposed so the confirmation dialog can SAY which one it is. A confirm
	 * that reads "remove?" for both is how an operator deletes a file they
	 * meant to unlink.
	 *
	 * @param string $dossierId The dossier object UUID.
	 * @param int $fileId The Nextcloud file node id.
	 *
	 * @return string Either 'trash' or 'unlink'.
	 *
	 * @spec openspec/changes/dossier-management-ui/specs/dossier-management-ui/spec.md
	 */
	public function removalMode(string $dossierId, int $fileId): string {
		try {
			$object = $this->requireReadable(dossierId: $dossierId);
			$payload = $this->payloadOf(object: $object);
		} catch (Throwable $e) {
			return 'unlink';
		}

		$node = $this->nodeFor(fileId: $fileId);
		if ($node === null) {
			return 'unlink';
		}

		$folder = $this->homeFolder(object: $object, payload: $payload);
		if ($folder === null || $this->isInFolder(node: $node, folder: $folder) === false) {
			return 'unlink';
		}

		if ($this->referencedElsewhere(dossierId: $dossierId, fileId: $fileId) === true) {
			return 'unlink';
		}

		return 'trash';

	}//end removalMode()

	// -----------------------------------------------------------------
	// Aggregation helpers
	// -----------------------------------------------------------------

	/**
	 * The dossier's effective membership: home folder ∪ documents[].
	 *
	 * @param object $object The dossier object.
	 * @param array<string, mixed> $payload Its payload.
	 *
	 * @return array{documents: array<int, array<string, mixed>>, missing: int} Members and the missing count.
	 */
	private function members(object $object, array $payload): array {
		$documents = [];
		$seen = [];

		$folder = $this->homeFolder(object: $object, payload: $payload);
		if ($folder !== null) {
			foreach ($this->enumerateFolder(folder: $folder) as $node) {
				$id = $node->getId();
				$seen[$id] = true;
				$documents[] = $this->documentRow(node: $node, referenced: false);
			}
		}

		$missing = 0;
		foreach ($this->documentRefs(payload: $payload) as $ref) {
			$fileId = (int)$ref;
			if (isset($seen[$fileId]) === true) {
				continue;
			}

			$node = $this->nodeFor(fileId: $fileId);
			if ($node === null) {
				// Visible, never silently dropped: a reference the caller
				// cannot resolve is information, not noise.
				$documents[] = [
					'id' => $fileId,
					'name' => '',
					'missing' => true,
					'referenced' => true,
				];
				$missing++;
				continue;
			}

			$seen[$fileId] = true;
			$documents[] = $this->documentRow(node: $node, referenced: true);
		}

		return ['documents' => $documents, 'missing' => $missing];

	}//end members()

	/**
	 * One document row.
	 *
	 * @param Node $node The file node.
	 * @param bool $referenced Whether it is a reference rather than a home-folder file.
	 *
	 * @return array<string, mixed> The row.
	 */
	private function documentRow(Node $node, bool $referenced): array {
		return [
			'id' => $node->getId(),
			'name' => $node->getName(),
			'mimetype' => $node->getMimetype(),
			'size' => $node->getSize(),
			'modified' => $node->getMTime(),
			'missing' => false,
			'referenced' => $referenced,
		];

	}//end documentRow()

	/**
	 * List the files in a folder under the caller's view.
	 *
	 * Deliberately NOT `FolderFileEnumerator::enumerate()`. That method builds
	 * an ANALYSIS QUEUE and filters out prior anonymisation outputs so a second
	 * run does not re-redact its own results. A dossier's document list is the
	 * opposite question — the operator wants to see everything the dossier
	 * holds, redacted copies included — so reusing it here would hide files
	 * from the very list that exists to show them.
	 *
	 * @param Folder $folder The dossier home folder.
	 *
	 * @return array<int, Node> The files, never folders.
	 */
	private function enumerateFolder(Folder $folder): array {
		try {
			return array_values(array_filter(
				$folder->getDirectoryListing(),
				static fn (Node $node): bool => $node->getType() === \OCP\Files\FileInfo::TYPE_FILE
			));
		} catch (Throwable $e) {
			$this->logger->warning(
				'DossierManagementService: cannot list the dossier folder',
				['exception' => $e->getMessage()]
			);
			return [];
		}

	}//end enumerateFolder()

	/**
	 * Resolve the dossier's grondslag slugs to their labels.
	 *
	 * An unknown slug is returned with `known: false` rather than dropped —
	 * a grondslag that silently disappears from a Woo dossier is a compliance
	 * problem, not a rendering detail.
	 *
	 * The lookup reads the `base` vocabulary directly. `BasesResolverService`
	 * looks the other way round — it answers "which grondslagen apply to this
	 * batch's folders" and returns slugs — so it cannot serve as the
	 * slug-to-label resolver this needs.
	 *
	 * @param array<string, mixed> $payload The dossier payload.
	 *
	 * @return array<int, array{slug: string, label: string, known: bool}> The resolved bases.
	 */
	private function resolveBases(array $payload): array {
		$slugs = ($payload['bases'] ?? []);
		if (is_array($slugs) === false || count($slugs) === 0) {
			return [];
		}

		$labels = $this->baseLabels();

		$out = [];
		foreach ($slugs as $slug) {
			$slug = (string)$slug;
			$label = ($labels[$slug] ?? '');

			$out[] = [
				'slug' => $slug,
				'label' => $this->firstNonEmpty(value: $label, fallback: $slug),
				'known' => ($label !== ''),
			];
		}

		return $out;

	}//end resolveBases()

	/**
	 * The `base` vocabulary as slug => name.
	 *
	 * @return array<string, string> The labels, empty when OpenRegister is unavailable.
	 */
	private function baseLabels(): array {
		if ($this->baseLabels !== null) {
			return $this->baseLabels;
		}

		$this->baseLabels = [];

		$objectService = $this->repository->objectService();
		if ($objectService === null) {
			return $this->baseLabels;
		}

		try {
			foreach ($this->findAllOf(objectService: $objectService, schema: 'base') as $base) {
				$payload = $this->payloadOf(object: $base);
				$slug = (string)($payload['@self']['slug'] ?? $payload['slug'] ?? '');
				if ($slug === '') {
					continue;
				}

				$this->baseLabels[$slug] = (string)($payload['name'] ?? $slug);
			}
		} catch (Throwable $e) {
			$this->logger->warning(
				'DossierManagementService: the grondslagen vocabulary could not be read',
				['exception' => $e->getMessage()]
			);
		}

		return $this->baseLabels;

	}//end baseLabels()

	/**
	 * The folder-batch runs recorded against this dossier's folder.
	 *
	 * @param object $object The dossier object.
	 * @param array<string, mixed> $payload Its payload.
	 *
	 * @return array<int, array<string, mixed>> The batch runs, newest first.
	 */
	private function batchRuns(object $object, array $payload): array {
		$objectService = $this->repository->objectService();
		if ($objectService === null) {
			return [];
		}

		$folder = $this->homeFolder(object: $object, payload: $payload);
		if ($folder === null) {
			return [];
		}

		try {
			$batches = $this->findAllOf(objectService: $objectService, schema: 'anonymizationBatch');
		} catch (Throwable $e) {
			return [];
		}

		$runs = [];
		foreach ($batches as $batch) {
			$batchPayload = $this->payloadOf(object: $batch);
			if ((int)($batchPayload['folderId'] ?? 0) !== $folder->getId()) {
				continue;
			}

			$runs[] = [
				'id' => (string)($batchPayload['batchId'] ?? ''),
				'status' => (string)($batchPayload['status'] ?? ''),
				'fileCount' => (int)($batchPayload['fileCount'] ?? 0),
				'created' => (string)($batchPayload['created'] ?? ''),
			];
		}

		usort($runs, static fn (array $a, array $b): int => strcmp($b['created'], $a['created']));

		return $runs;

	}//end batchRuns()

	/**
	 * The publication section, presence-gated on the Woo pipeline.
	 *
	 * @return array{installed: bool, state: string} The publication state.
	 */
	private function publication(): array {
		// The woo-publicatie-pipeline has not shipped. Reporting `installed:
		// false` lets the detail explain the capability is absent instead of
		// offering a publish action that would fail.
		return ['installed' => false, 'state' => ''];

	}//end publication()

	// -----------------------------------------------------------------
	// Object + filesystem helpers
	// -----------------------------------------------------------------

	/**
	 * The dossier object, or a refusal.
	 *
	 * ⚠️ The guard is only as strong as the schema's cascade. `dossier`
	 * declares `read: ["authenticated"]`, so OpenRegister admits any
	 * authenticated user in the organisation and this resolves to an existence
	 * test. That is enforced HERE rather than bypassed: passing `_rbac: false`
	 * would make the endpoint unconditionally open, and tightening the cascade
	 * is a separate decision (ConductionNL/filinq#441). The FILE half is real —
	 * every listing below runs through the caller's own view.
	 *
	 * @param string $dossierId The dossier object UUID.
	 *
	 * @return object The dossier object.
	 *
	 * @throws RuntimeException When it is absent or unreadable.
	 */
	private function requireReadable(string $dossierId): object {
		$objectService = $this->requireObjectService();

		$object = $objectService->find(
			id: $dossierId,
			register: self::REGISTER,
			schema: self::SCHEMA
		);

		if ($object === null) {
			throw new RuntimeException('Dossier not found, or you cannot read it: ' . $dossierId, 404);
		}

		return $object;

	}//end requireReadable()

	/**
	 * OpenRegister's object service, or a refusal.
	 *
	 * @return object The object service.
	 *
	 * @throws RuntimeException When OpenRegister is unavailable.
	 */
	private function requireObjectService(): object {
		$objectService = $this->repository->objectService();
		if ($objectService === null) {
			throw new RuntimeException('OpenRegister is not available.', 503);
		}

		return $objectService;

	}//end requireObjectService()

	/**
	 * Write changes onto a dossier as a FULL payload.
	 *
	 * @param object $object The dossier object.
	 * @param array<string, mixed> $payload Its current payload.
	 * @param array<string, mixed> $changes The fields to change.
	 *
	 * @return void
	 *
	 * @throws RuntimeException When the save is refused.
	 */
	private function save(object $object, array $payload, array $changes): void {
		$objectService = $this->requireObjectService();

		// Everything forward, then the change on top. OR saves are
		// PUT-semantic: posting only the changed key nulls the rest.
		$next = ($payload + []);
		foreach ($changes as $key => $value) {
			$next[$key] = $value;
		}

		$self = ($payload['@self'] ?? []);
		if (is_array($self) === false) {
			$self = [];
		}

		$self['register'] = self::REGISTER;
		$self['schema'] = self::SCHEMA;
		$next['@self'] = $self;

		try {
			$objectService->saveObject(
				object: $next,
				register: self::REGISTER,
				schema: self::SCHEMA
			);
		} catch (Throwable $e) {
			// A lifecycle refusal arrives here. Surfacing the message keeps
			// the rejection readable instead of a bare 500.
			throw new RuntimeException($e->getMessage(), 409, $e);
		}

	}//end save()

	/**
	 * Create the dossier's home folder under the caller's Filinq directory.
	 *
	 * @param string $name The dossier name.
	 *
	 * @return Folder The created (or existing) folder.
	 *
	 * @throws RuntimeException When it cannot be created.
	 */
	private function createHomeFolder(string $name): Folder {
		$user = $this->userSession->getUser();
		if ($user === null) {
			throw new RuntimeException('Not authenticated.', 401);
		}

		try {
			$userFolder = $this->rootFolder->getUserFolder($user->getUID());
			if ($userFolder->nodeExists('Filinq') === true) {
				$parent = $userFolder->get('Filinq');
			} else {
				$parent = $userFolder->newFolder('Filinq');
			}

			if (($parent instanceof Folder) === false) {
				throw new RuntimeException('Filinq is not a folder.', 500);
			}

			$safe = $this->safeFolderName(name: $name);

			if ($parent->nodeExists($safe) === true) {
				return $parent->get($safe);
			}

			return $parent->newFolder($safe);
		} catch (Throwable $e) {
			throw new RuntimeException('Could not create the dossier folder: ' . $e->getMessage(), 500, $e);
		}

	}//end createHomeFolder()

	/**
	 * Rename the bound home folder to match the dossier, best-effort.
	 *
	 * @param object $object The dossier object.
	 * @param array<string, mixed> $payload Its payload.
	 * @param string $name The new name.
	 *
	 * @return string A readable warning, or '' when the folder was renamed.
	 */
	private function renameHomeFolder(object $object, array $payload, string $name): string {
		$folder = $this->homeFolder(object: $object, payload: $payload);
		if ($folder === null) {
			return '';
		}

		$safe = $this->safeFolderName(name: $name);
		if ($folder->getName() === $safe) {
			return '';
		}

		try {
			$parent = $folder->getParent();
			if ($parent->nodeExists($safe) === true) {
				// Never merge or overwrite. The object rename still stands.
				return sprintf('The folder was not renamed: "%s" already exists here.', $safe);
			}

			$folder->move($parent->getPath() . '/' . $safe);

			return '';
		} catch (NotPermittedException $e) {
			return 'The folder was not renamed: you do not have permission to rename it.';
		} catch (Throwable $e) {
			$this->logger->warning(
				'DossierManagementService: bound folder rename failed',
				['exception' => $e->getMessage()]
			);

			return 'The folder was not renamed: ' . $e->getMessage();
		}

	}//end renameHomeFolder()

	/**
	 * A folder name safe to write to disk.
	 *
	 * @param string $name The dossier name.
	 *
	 * @return string The sanitised name.
	 */
	private function safeFolderName(string $name): string {
		$safe = trim(str_replace(['/', '\\'], '-', $name));

		return $this->firstNonEmpty(value: $safe, fallback: 'Dossier');

	}//end safeFolderName()

	/**
	 * The dossier's bound home folder, under the caller's view.
	 *
	 * @param object $object The dossier object.
	 * @param array<string, mixed> $payload Its payload.
	 *
	 * @return Folder|null The folder, or null when it is gone or unreadable.
	 */
	private function homeFolder(object $object, array $payload): ?Folder {
		try {
			$ref = ($payload['@self']['folder'] ?? null);
			if ($ref === null && method_exists($object, 'getFolder') === true) {
				$ref = $object->getFolder();
			}

			if ($ref === null || $ref === '') {
				return null;
			}

			return $this->repository->resolveDossierFolder(folderRef: $ref);
		} catch (Throwable $e) {
			return null;
		}

	}//end homeFolder()

	/**
	 * Resolve a file node under the caller's view.
	 *
	 * @param int $fileId The Nextcloud file node id.
	 *
	 * @return Node|null The node, or null when absent or unreadable.
	 */
	private function nodeFor(int $fileId): ?Node {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return null;
		}

		try {
			$nodes = $this->rootFolder->getUserFolder($user->getUID())->getById($fileId);

			return ($nodes[0] ?? null);
		} catch (NotFoundException | Throwable $e) {
			return null;
		}

	}//end nodeFor()

	/**
	 * Whether a node sits inside a folder.
	 *
	 * @param Node $node The node.
	 * @param Folder $folder The folder.
	 *
	 * @return bool True when the node is inside.
	 */
	private function isInFolder(Node $node, Folder $folder): bool {
		try {
			return str_starts_with($node->getPath(), rtrim($folder->getPath(), '/') . '/');
		} catch (Throwable $e) {
			return false;
		}

	}//end isInFolder()

	/**
	 * Whether another dossier also references this file.
	 *
	 * @param string $dossierId The dossier being removed from.
	 * @param int $fileId The Nextcloud file node id.
	 *
	 * @return bool True when at least one other dossier references it.
	 */
	private function referencedElsewhere(string $dossierId, int $fileId): bool {
		$objectService = $this->repository->objectService();
		if ($objectService === null) {
			return false;
		}

		try {
			$objects = $this->findAllOf(objectService: $objectService, schema: self::SCHEMA);
		} catch (Throwable $e) {
			// Fail SAFE: unable to prove the file is unreferenced, so treat it
			// as referenced and unlink rather than trash.
			return true;
		}

		foreach ($objects as $object) {
			$payload = $this->payloadOf(object: $object);
			if ($this->uuidOf(object: $object, payload: $payload) === $dossierId) {
				continue;
			}

			if (in_array((string)$fileId, $this->documentRefs(payload: $payload), true) === true) {
				return true;
			}
		}

		return false;

	}//end referencedElsewhere()

	/**
	 * Move a file to the trashbin.
	 *
	 * @param int $fileId The Nextcloud file node id.
	 *
	 * @return void
	 */
	private function trash(int $fileId): void {
		$node = $this->nodeFor(fileId: $fileId);
		if ($node === null) {
			return;
		}

		try {
			// NC's delete() routes through the trashbin when files_trashbin is
			// enabled, which is what makes this recoverable.
			$node->delete();
		} catch (Throwable $e) {
			$this->logger->warning(
				'DossierManagementService: could not remove the document',
				['fileId' => $fileId, 'exception' => $e->getMessage()]
			);
		}

	}//end trash()

	// -----------------------------------------------------------------
	// Payload helpers
	// -----------------------------------------------------------------

	/**
	 * The object's payload as an array.
	 *
	 * @param object $object The OpenRegister object.
	 *
	 * @return array<string, mixed> The payload.
	 */
	private function payloadOf(object $object): array {
		if (method_exists($object, 'jsonSerialize') === true) {
			$payload = $object->jsonSerialize();
			if (is_array($payload) === true) {
				return $payload;
			}
		}

		if (method_exists($object, 'getObject') === true) {
			$payload = $object->getObject();
			if (is_array($payload) === true) {
				return $payload;
			}
		}

		return (array)$object;

	}//end payloadOf()

	/**
	 * The object's UUID.
	 *
	 * @param object $object The OpenRegister object.
	 * @param array<string, mixed> $payload Its payload.
	 *
	 * @return string The UUID, or '' when it has none.
	 */
	private function uuidOf(object $object, array $payload): string {
		$uuid = ($payload['@self']['id'] ?? $payload['id'] ?? $payload['uuid'] ?? '');
		if ($uuid === '' && method_exists($object, 'getUuid') === true) {
			$uuid = $object->getUuid();
		}

		return (string)$uuid;

	}//end uuidOf()

	/**
	 * The dossier's status, defaulting for objects that predate the property.
	 *
	 * @param array<string, mixed> $payload The dossier payload.
	 *
	 * @return string The status.
	 */
	private function statusOf(array $payload): string {
		$status = trim((string)($payload['status'] ?? ''));

		return $this->firstNonEmpty(value: $status, fallback: self::DEFAULT_STATUS);

	}//end statusOf()

	/**
	 * The dossier's explicit membership references.
	 *
	 * @param array<string, mixed> $payload The dossier payload.
	 *
	 * @return array<int, string> The references.
	 */
	private function documentRefs(array $payload): array {
		$refs = ($payload['documents'] ?? []);
		if (is_array($refs) === false) {
			return [];
		}

		return array_values(array_map(static fn ($ref): string => (string)$ref, $refs));

	}//end documentRefs()

	/**
	 * Every object of one schema in this app's register.
	 *
	 * `ObjectService::findAll()` takes a CONFIG ARRAY, not `register:` /
	 * `schema:` named arguments — those exist on `find()` and `saveObject()`
	 * but not here. Calling it the other way throws "Unknown named parameter
	 * $register" at runtime, which every guarded caller then swallows.
	 *
	 * @param object $objectService OpenRegister's object service.
	 * @param string $schema The schema slug.
	 *
	 * @return array<int, object> The objects.
	 */
	private function findAllOf(object $objectService, string $schema): array {
		return $objectService->findAll(
			config: [
				'filters' => [
					'register' => self::REGISTER,
					'schema' => $schema,
				],
			]
		);

	}//end findAllOf()

	/**
	 * The first value that is not an empty string.
	 *
	 * @param string $value The preferred value.
	 * @param string $fallback The value to use when $value is empty.
	 *
	 * @return string Whichever is non-empty.
	 */
	private function firstNonEmpty(string $value, string $fallback): string {
		if ($value !== '') {
			return $value;
		}

		return $fallback;

	}//end firstNonEmpty()

}//end class
