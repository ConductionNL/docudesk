<?php

/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
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
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Service for anonymizing sensitive information in documents
 *
 * This service handles the anonymization of sensitive information in documents
 * using Presidio for entity detection and replacement.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
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
    private readonly ReportingService $reportingService;

    /**
     * Constructor for AnonymizationService
     *
     * @param LoggerInterface   $logger            Logger for error reporting
     * @param IConfig           $config            Configuration service
     * @param ObjectService     $objectService     Service for storing objects
     * @param ExtractionService $extractionService Service for extracting text from documents
     * @param IUserSession      $userSession       User session for getting current user
     * @param ReportingService  $reportingService  Service for generating reports
     *
     * @return void
     */
    public function __construct(
        LoggerInterface $logger,
        IConfig $config,
        ObjectService $objectService,
        ExtractionService $extractionService,
        IUserSession $userSession,
        ReportingService $reportingService
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->objectService = $objectService;
        $this->extractionService = $extractionService;
        $this->userSession = $userSession;
        $this->reportingService = $reportingService;
        
        // Initialize Guzzle HTTP client
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Process anonymization for a document based on a report
     *
     * This method checks if anonymization is needed based on the file hash/etag,
     * creates a new anonymized file with the same name plus "_anonymized",
     * and replaces entities with [entityType: key] format.
     *
     * @param \OCP\Files\Node $node   The file node to anonymize
     * @param array<string, mixed>|null $report The report containing detected entities (optional)
     *
     * @return array<string, mixed> The anonymization result
     *
     * @throws Exception If anonymization fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    public function processAnonymization(\OCP\Files\Node $node, ?array $report = null): array
    {
        $startTime = microtime(true);

        /*
        // If no report is provided, try to get one
        if ($report === null) {
            $this->logger->debug('No report provided, trying to get existing report');
            
            // Try to get existing report
            $report = $this->reportingService->getReport($node);
            
            // If no report exists, create one
            if ($report === null) {
                $this->logger->debug('No existing report found, creating new report');
                $report = $this->reportingService->createReport($node);
            }
            
            // If report is not completed, process it
            if ($report['status'] !== 'completed') {
                $this->logger->debug('Report not completed, processing report');
                $report = $this->reportingService->processReport($report);
            }
        }
        */
        
        // Use ETag as file hash if available, otherwise calculate hash
        $fileHash = null;
        if (method_exists($node, 'getEtag')) {
            $fileHash = $node->getEtag();
            $this->logger->debug('Using ETag as file hash: ' . $fileHash);
        } else {
            // Fall back to calculating hash
            $fileHash = $this->reportingService->calculateFileHash($node->getPath());
        }

        // Check if anonymization already exists for this node
        $anonymization = $this->getAnonymization($node);
        if ($anonymization === null) {
            // Initialize base anonymization result array only if no existing anonymization found
            $anonymization = [
                'nodeId' => $node->getId(),
                'fileHash' => $report['fileHash'] ?? '',
                'originalFileName' => $report['fileName'] ?? $node->getName(),
                'anonymizedFileName' => '',
                'anonymizedFilePath' => '',
                'entities' => [],
                'replacements' => [],
                'startTime' => $startTime,
                'endTime' => null,
                'processingTime' => null,
                'status' => 'pending',
                'message' => ''
            ];
        }

        // Lets return the anonymization if the hash is the same
        if ($anonymization['fileHash'] === $fileHash) {
            $this->logger->debug('File hash matches existing anonymization, returning cached result', [
                'fileHash' => $fileHash,
                'anonymizationId' => $anonymization['id'] ?? null
            ]);
            return $anonymization;
        }        

        // Check if anonymization is needed (if there are entities)
        if (empty($report['entities'])) {
            $this->logger->info('No entities detected for anonymization in document: ' . $node->getPath());            
            // Update result array
            $anonymization['status'] = 'completed';
            $anonymization['message'] = 'No entities detected for anonymization';
            $anonymization['endTime'] = microtime(true);
            $anonymization['processingTime'] = $anonymizationResult['endTime'] - $startTime;
            
            // Save the anonymization result before returning
            $anonymization = $this->objectService->saveObject('anonymization', $anonymization);
            
            return $anonymization;
        }

        // Get the file content
        $content = $node->getContent();
        if (empty($content)) {
            throw new Exception('Failed to get content from file: ' . $node->getPath());
        }

        // Create a new file name with "_anonymized" suffix
        $fileName = $node->getName();
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $anonymizedFileName = $fileNameWithoutExtension . '_anonymized';
        if (!empty($fileExtension)) {
            $anonymizedFileName .= '.' . $fileExtension;
        }
        
        // Get the parent folder
        $parentFolder = $node->getParent();
                    
        // Check if anonymized file already exists and delete it
        try {
            if ($parentFolder->nodeExists($anonymizedFileName)) {
                $parentFolder->get($anonymizedFileName)->delete();
                $this->logger->debug('Deleted existing anonymized file: ' . $anonymizedFileName);
            }
        } catch (Exception $e) {
            $this->logger->warning('Failed to delete existing anonymized file: ' . $e->getMessage(), ['exception' => $e]);
        }

        // Anonymize the content by replacing entities with [entityType: key]
        $anonymizedContent = $content;
        $replacements = [];
        
        // Add unique keys to entities and sort by start position in descending order
        $entities = array_map(function($entity) {
            $entity['key'] = substr(Uuid::v4()->toRfc4122(), 0, 8);
            return $entity;
        }, $report['entities']);
        
        usort($entities, function ($a, $b) {
            return ($b['start'] ?? 0) - ($a['start'] ?? 0);
        });
        
        foreach ($entities as $entity) {
            $entityType = $entity['entityType'] ?? 'UNKNOWN';
            $entityText = $entity['text'] ?? '';
            $start = $entity['start'] ?? null;
            $end = $entity['end'] ?? null;
            $key = $entity['key'];
            
            // Skip if we don't have position information
            if ($start === null || $end === null || empty($entityText)) {
                continue;
            }
            
            // Create replacement text
            $replacementText = '[' . $entityType . ': ' . $key . ']';
            
            // Replace the entity in the content
            $anonymizedContent = substr_replace($anonymizedContent, $replacementText, $start, $end - $start);
            
            // Record the replacement
            $replacements[] = [
                'entity_type' => $entityType,
                'original_text' => $entityText,
                'replacement_text' => $replacementText,
                'key' => $key,
                'start' => $start,
                'end' => $end
            ];
        }
        
        // Create the anonymized file
        $newFile = $parentFolder->newFile($anonymizedFileName, $anonymizedContent);
        
        // Create anonymization log
        //$anonymizationKey = $this->generateAnonymizationKey();

        // Add file hash to the log
        //$anonymization['anonymizationKey'] = $anonymizationKey;
        $anonymization['fileHash'] = $fileHash;
        $anonymization['replacements'] = $replacements;
        $anonymization['anonymizedFileId'] = $newFile->getId();
        $anonymization['anonymizedFilePath'] = $newFile->getPath();
        
        // Save the updated log
        return $this->objectService->saveObject('anonymization', $anonymization);
    }

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
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function getAnonymization(\OCP\Files\Node $node): ?array
    {
        // Validate that the node is a file
        if ($node->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
            throw new \InvalidArgumentException('Node must be a file to get anonymization data');
        }

        try {
            $anonymizationObjectType = 'anonymization';
            
            $filters = [
                'nodeId' => $node->getId()
            ];

            $anonymizations = $this->objectService->getObjects($anonymizationObjectType, null, 0, $filters);
            
            // Throw error if multiple anonymizations found
            if (count($anonymizations) > 1) {
                throw new \RuntimeException('Multiple anonymizations found for node ' . $node->getId() . '. There should only be one anonymization per node.');
            }
            
            return !empty($anonymizations) ? $anonymizations[0] : null;
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve anonymization: ' . $e->getMessage(), [
                'nodeId' => $node->getId(),
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Delete an anonymization by ID
     *
     * @param string $anonymizationId ID of the anonymization to delete
     *
     * @return bool True if deletion was successful, false otherwise
     *
     * @psalm-return bool
     * @phpstan-return bool
     */
    public function deleteAnonymization(string $anonymizationId): bool
    {
        try {
            return $this->objectService->deleteObject('anonymization', $anonymizationId);
        } catch (Exception $e) {
            $this->logger->error('Failed to delete anonymization: ' . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * Get anonymization by ID
     *
     * @param string $anonymizationId The ID of the anonymization to retrieve
     *
     * @return array<string, mixed>|null The anonymization data or null if not found
     *
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function getAnonymizationById(string $anonymizationId): ?array
    {
        try {
            return $this->objectService->getObject('anonymization', $anonymizationId);
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve anonymization by ID: ' . $e->getMessage(), [
                'anonymizationId' => $anonymizationId,
                'exception' => $e
            ]);
            return null;
        }
    }
}
