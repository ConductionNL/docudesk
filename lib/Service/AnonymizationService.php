<?php

/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2
 *
 * DocuDesk is free software: you can redistribute it and/or modify
 * it under the terms of the European Union Public License (EUPL),
 * version 1.2 only (the "Licence"), appearing in the file LICENSE
 * included in the packaging of this file.
 *
 * DocuDesk is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * European Union Public License for more details.
 *
 * You should have received a copy of the European Union Public License
 * along with DocuDesk. If not, see <https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12>.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\Service;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OCP\IConfig;
use OCP\IAppConfig;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use OCA\OpenRegister\Service\ObjectService;

/**
 * Service for anonymizing sensitive information in documents
 *
 * This service handles the anonymization of sensitive information in documents
 * using Presidio for entity detection and replacement.
 */
class AnonymizationService
{
    /**
     * Default Presidio API URL if not specified in configuration
     *
     * @var string
     */
    private const DEFAULT_PRESIDIO_ANALYZER_URL = 'http://presidio-api:8080/analyze';

    /**
     * Default Presidio Anonymizer API URL if not specified in configuration
     *
     * @var string
     */
    private const DEFAULT_PRESIDIO_ANONYMIZER_URL = 'http://presidio-api:8080/anonymize';

    /**
     * Default confidence threshold for entity detection
     *
     * @var float
     */
    private const DEFAULT_CONFIDENCE_THRESHOLD = 0.7;

    /**
     * Logger instance for error reporting
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * HTTP client for API requests
     *
     * @var Client
     */
    private readonly Client $client;

    /**
     * Configuration service
     *
     * @var IConfig
     */
    private readonly IConfig $config;

    /**
     * Object service for storing anonymization data
     *
     * @var ObjectService
     */
    private readonly ObjectService $objectService;

    /**
     * Extraction service for getting text from documents
     *
     * @var ExtractionService
     */
    private readonly ExtractionService $extractionService;

    /**
     * User session for getting current user
     *
     * @var IUserSession
     */
    private readonly IUserSession $userSession;

    /**
     * Reporting service for getting reports
     *
     * @var ReportingService
     */
    private ReportingService $reportingService;


    /**
     * App config for getting app config
     *
     * @var IAppConfig
     */
    private readonly IAppConfig $appConfig;

    /**
     * Constructor for AnonymizationService
     *
     * @param LoggerInterface   $logger            Logger for error reporting
     * @param IConfig           $config            Configuration service
     * @param ObjectService     $objectService     Service for storing objects
     * @param ExtractionService $extractionService Service for extracting text from documents
     * @param IUserSession      $userSession       User session for getting current user
     *
     * @return void
     */
    public function __construct(
        LoggerInterface $logger,
        IConfig $config,
        ObjectService $objectService,
        ExtractionService $extractionService,
        IUserSession $userSession,
        IAppConfig $appConfig
    ) {
        $this->logger            = $logger;
        $this->config            = $config;
        $this->objectService     = $objectService;
        $this->extractionService = $extractionService;
        $this->userSession = $userSession;
        $this->appConfig = $appConfig;

        

        // Set the object service to use the reporting service
        $reportRegisterType = $this->appConfig->getValueString(
            'DocuDesk', 
            'anonymization_register', 
            'document'
        );
        $this->objectService->setRegister($reportRegisterType);
        
        $reportObjectType = $this->appConfig->getValueString(
            'DocuDesk', 
            'anonymization_schema', 
            'anonymization'
        );
        $this->objectService->setSchema($reportObjectType);

        // Initialize Guzzle HTTP client
        $this->client = new Client(
            [
            'timeout' => 30,
            'connect_timeout' => 5,
            ]
        );
    }

    }//end __construct()


    /**
     * Set the reporting service
     *
     * This method is used to set the reporting service after construction
     * to avoid circular dependencies.
     *
     * @param ReportingService $reportingService Service for generating reports
     *
     * @return void
     *
     * @psalm-return   void
     * @phpstan-return void
     */
    public function setReportingService(ReportingService $reportingService): void
    {
        $this->reportingService = $reportingService;

    }//end setReportingService()


    /**
     * Process anonymization for a document based on a report
     *
     * This method checks if anonymization is needed based on the file hash/etag,
     * creates a new anonymized file with the same name plus "_anonymized",
     * and replaces entities with [entityType: key] format.
     *
     * @param \OCP\Files\Node           $node   The file node to anonymize
     * @param array<string, mixed>|null $report The report containing detected entities (optional)
     *
     * @return array<string, mixed>|void The anonymization result or void if no anonymization needed
     *
     * @throws Exception If anonymization fails
     *
     * @psalm-return   array<string, mixed>|void
     * @phpstan-return array<string, mixed>|void
     */
    public function processAnonymization(\OCP\Files\Node $node, ?array $report=null)
    {
        $startTime = microtime(true);

        // If no report is provided, try to get one.
        if ($report === null) {
            $this->logger->debug('No report provided, trying to get existing report');

            // Try to get existing report.
            $report = $this->reportingService->getReport($node);

            // If no report exists, create one.
            if ($report === null) {
                $this->logger->debug('No existing report found, creating new report');
                $report = $this->reportingService->createReport($node);
            }

            // If report is not completed, process it.
            if ($report['status'] !== 'completed') {
                $this->logger->debug('Report not completed, processing report');
                $report = $this->reportingService->processReport($report);
            }
        }

        // If the file name end in _anonymized, we can return the anonymization result @todo we should solve this with taging or something else, this is touch and go it should also be handled in the porccesReport function or something else.
        // Create a new file name with "_anonymized" suffix.
        $fileName      = $node->getName();
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $anonymizedFileName       = $fileNameWithoutExtension.'_anonymized';
        if (!empty($fileExtension)) {
            $anonymizedFileName .= '.'.$fileExtension;
        }

        if (str_ends_with($fileNameWithoutExtension, '_anonymized')) {
            $this->logger->debug('File name ends with _anonymized, returning anonymization result');
            return;
        }

        // Use ETag as file hash if available, otherwise calculate hash.
        $fileHash = null;
        if (method_exists($node, 'getEtag')) {
            $fileHash = $node->getEtag();
            $this->logger->debug('Using ETag as file hash: '.$fileHash);
        } else {
            // Fall back to calculating hash.
            $fileHash = $this->reportingService->calculateFileHash($node->getPath());
        }

        // Check if anonymization already exists for this node.
        $anonymization = $this->getAnonymization($node);
        if ($anonymization === null) {
            // Initialize base anonymization result array only if no existing anonymization found.
            $anonymization = [
                'nodeId'             => $node->getId(),
                'fileHash'           => $report['fileHash'] ?? $fileHash,
                'originalFileName'   => $report['fileName'] ?? $node->getName(),
                'anonymizedFileName' => '',
                'anonymizedFilePath' => '',
                'entities'           => [],
                'replacements'       => [],
                'startTime'          => $startTime,
                'endTime'            => null,
                'processingTime'     => null,
                'status'             => 'pending',
                'message'            => '',
            ];
        }

        // Lets return the anonymization if the hash is the same.
        if ($anonymization['fileHash'] === $fileHash && $anonymization['status'] === 'completed') {
            $this->logger->debug(
                'File hash matches existing anonymization, returning cached result', [
                'fileHash' => $fileHash,
                'anonymizationId' => $anonymization['id'] ?? null
                ]
            );
            // Save the anonymization result before returning
            $anonymization['message'] = 'File hash matches existing anonymization, returning cached result';
            $anonymization            = $this->objectService->saveObject('anonymization', $anonymization);
            return $anonymization;
        }

        // Check if anonymization is needed (if there are entities).
        if (empty($report['entities'])) {
            $this->logger->info('No entities detected for anonymization in document: '.$node->getPath());

            // Update result array.
            $anonymization['status']         = 'completed';
            $anonymization['message']        = 'No entities detected for anonymization in document: '.$node->getPath();
            $anonymization['endTime']        = microtime(true);
            $anonymization['processingTime'] = $anonymization['endTime'] - $startTime;

            // Save the anonymization result before returning.
            $anonymization = $this->objectService->saveObject('anonymization', $anonymization);

            return $anonymization;
        }

        // Update anonymization with entities from report.
        $anonymization['entities'] = $report['entities'];
        $anonymization['status']   = 'processing';

        // Save the updated log.
        // $anonymization = $this->objectService->saveObject('anonymization', $anonymization);       
        // Get the file content.
        $content = $node->getContent();
        if (empty($content)) {
            throw new Exception('Failed to get content from file: '.$node->getPath());
        }

        // Get the parent folder
        $parentFolder = $node->getParent();

        // Check if anonymized file already exists and delete it.
        try {
            if ($parentFolder->nodeExists($anonymizedFileName)) {
                $parentFolder->get($anonymizedFileName)->delete();
                $this->logger->debug('Deleted existing anonymized file: '.$anonymizedFileName);
            }
        } catch (Exception $e) {
            $this->logger->warning('Failed to delete existing anonymized file: '.$e->getMessage(), ['exception' => $e]);
        }

        // Anonymize the content by replacing entities with [entityType: key].
        $anonymizedContent = $content;
        $replacements      = [];

        // Process entities and find their positions in the content if not provided.
        $processedEntities = [];
        foreach ($report['entities'] as $entity) {
            $entityType = $entity['entityType'] ?? 'UNKNOWN';
            $entityText = $entity['text'] ?? '';
            $score      = $entity['score'] ?? 0;

            // Skip if we don't have text.
            if (empty($entityText)) {
                continue;
            }

            // If start and end positions are not provided, find them in the content.
            if (!isset($entity['start']) || !isset($entity['end'])) {
                // Find all occurrences of the entity text in the content.
                $offset = 0;
                while (($pos = mb_stripos($content, $entityText, $offset)) !== false) {
                    $processedEntities[] = [
                        'entityType' => $entityType,
                        'text'       => $entityText,
                        'score'      => $score,
                        'start'      => $pos,
                        'end'        => $pos + mb_strlen($entityText),
                        'key'        => substr(Uuid::v4()->toRfc4122(), 0, 8),
                    ];
                    $offset = $pos + mb_strlen($entityText);
                }
            } else {
                // Use the provided positions.
                $processedEntities[] = [
                    'entityType' => $entityType,
                    'text'       => $entityText,
                    'score'      => $score,
                    'start'      => $entity['start'],
                    'end'        => $entity['end'],
                    'key'        => substr(Uuid::v4()->toRfc4122(), 0, 8),
                ];
            }
        }
        
        // Sort entities by start position in descending order to avoid position shifts
        usort(
            $processedEntities, function ($a, $b) {
                return ($b['start'] ?? 0) - ($a['start'] ?? 0);
            }
        );
        
        // Log the processed entities for debugging
        $this->logger->debug(
            'Processed entities for anonymization:', [
            'processedEntities' => $processedEntities,
            'originalEntities' => $report['entities']
            ]
        );
        
        // Replace entities in the content
        foreach ($processedEntities as $entity) {
            $entityType = $entity['entityType'];
            $entityText = $entity['text'];
            $start      = $entity['start'];
            $end        = $entity['end'];
            $key        = $entity['key'];

            // Create replacement text.
            $replacementText = '['.$entityType.': '.$key.']';

            // Replace the entity in the content.
            $anonymizedContent = substr_replace($anonymizedContent, $replacementText, $start, $end - $start);

            // Record the replacement.
            $replacements[] = [
                'entityType'      => $entityType,
                'originalText'    => $entityText,
                'replacementText' => $replacementText,
                'key'             => $key,
                'start'           => $start,
                'end'             => $end,
            ];
        }//end foreach

        // Create the anonymized file.
        $newFile = $parentFolder->newFile($anonymizedFileName, $anonymizedContent);

        // Update anonymization object.
        $endTime = microtime(true);
        $anonymization['status']       = 'completed';
        $anonymization['message']      = 'Anonymization completed successfully';
        $anonymization['replacements'] = $replacements;
        $anonymization['anonymizedFileName'] = $anonymizedFileName;
        $anonymization['anonymizedFilePath'] = $newFile->getPath();
        $anonymization['endTime']            = $endTime;
        $anonymization['processingTime']     = $endTime - $startTime;

        // Save the updated log
        return $this->objectService->saveObject('anonymization', $anonymization);

    }//end processAnonymization()


    /**
     * Get anonymization for a node
     *
     * This method retrieves the anonymization data for a specific file node.
     *
     * @param \OCP\Files\Node $node The file node to get the anonymization for
     *
     * @return array<string, mixed>|null The anonymization data or null if not found
     *
     * @throws \InvalidArgumentException If the node is not a file
     * @throws \RuntimeException If multiple anonymizations are found for the node
     *
     * @psalm-return   array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function getAnonymization(\OCP\Files\Node $node): ?array
    {
        // Validate that the node is a file.
        if ($node->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
            throw new \InvalidArgumentException('Node must be a file to get anonymization data');
        }

        try {
            $anonymizationObjectType = 'anonymization';

            $filters = [
                'nodeId' => $node->getId(),
            ];

            $anonymizations = $this->objectService->getObjects($anonymizationObjectType, null, 0, $filters);

            // Throw error if multiple anonymizations found.
            if (count($anonymizations) > 1) {
                throw new \RuntimeException('Multiple anonymizations found for node '.$node->getId().'. There should only be one anonymization per node.');
            }

            return !empty($anonymizations) ? $anonymizations[0] : null;
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to retrieve anonymization: ' . $e->getMessage(), [
                'nodeId' => $node->getId(),
                'exception' => $e
                ]
            );
            return null;
        }//end try

    }//end getAnonymization()


    /**
     * Delete an anonymization by ID
     *
     * @param string $anonymizationId ID of the anonymization to delete
     *
     * @return bool True if deletion was successful, false otherwise
     *
     * @psalm-return   bool
     * @phpstan-return bool
     */
    public function deleteAnonymization(string $anonymizationId): bool
    {
        try {
            return $this->objectService->deleteObject('anonymization', $anonymizationId);
        } catch (Exception $e) {
            $this->logger->error('Failed to delete anonymization: '.$e->getMessage(), ['exception' => $e]);
            return false;
        }

    }//end deleteAnonymization()


    /**
     * Get anonymization by ID
     *
     * @param string $anonymizationId The ID of the anonymization to retrieve
     *
     * @return array<string, mixed>|null The anonymization data or null if not found
     *
     * @psalm-return   array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function getAnonymizationById(string $anonymizationId): ?array
    {
        try {
            return $this->objectService->getObject('anonymization', $anonymizationId);
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to retrieve anonymization by ID: ' . $e->getMessage(), [
                'anonymizationId' => $anonymizationId,
                'exception' => $e
                ]
            );
            return null;
        }

    }//end getAnonymizationById()


}//end class
