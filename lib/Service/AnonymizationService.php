<?php
/**
 * DocuDesk is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * DocuDesk is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * EUPL-1.2 License for more details.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link     https://www.DocuDesk.nl
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
     * Schema type for anonymization objects
     *
     * @var string
     */
    private readonly string $anonymizationSchemaType;

    /**
     * Register type for anonymization objects
     *
     * @var string
     */
    private readonly string $anonymizationRegisterType;

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
        $this->logger = $logger;
        $this->config = $config;
        $this->objectService = $objectService;
        $this->extractionService = $extractionService;
        $this->userSession = $userSession;
        $this->appConfig = $appConfig;
        
        $this->anonymizationSchemaType = $this->appConfig->getValueString(
            'DocuDesk', 
            'anonymization_schema', 
            'anonymization'
        );

        $this->anonymizationRegisterType = $this->appConfig->getValueString(
            'DocuDesk', 
            'anonymization_register', 
            'document'
        );
        
        $this->objectService->setRegister($this->anonymizationRegisterType);
        $this->objectService->setSchema($this->anonymizationSchemaType);
     

        // Initialize Guzzle HTTP client
        $this->client = new Client(
            [
            'timeout' => 30,
            'connect_timeout' => 5,
            ]
        );
    }

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
    }

    /**
     * Anonymize a Word document by replacing detected entities in the document structure
     *
     * @param \OCP\Files\Node $node The file node to anonymize
     * @param array $processedEntities The processed entities with replacement info
     * @param string $anonymizedFileName The name for the anonymized file
     * @return \OCP\Files\File The new anonymized file node
     *
     * @throws Exception If anonymization fails
     *
     * @psalm-return   \OCP\Files\File
     * @phpstan-return \OCP\Files\File
     */
    private function anonymizeWordDocument(
        \OCP\Files\Node $node,
        array $processedEntities,
        string $anonymizedFileName
    ): \OCP\Files\File {
        // Get the file content as a stream and save to a temp file
        $stream = $node->fopen('r');
        $tempFile = tempnam(sys_get_temp_dir(), 'docudesk_word_');
        if ($tempFile === false) {
            throw new Exception('Failed to create temporary file');
        }
        $tempStream = fopen($tempFile, 'w');
        if ($tempStream === false) {
            unlink($tempFile);
            throw new Exception('Failed to open temporary file for writing');
        }
        stream_copy_to_stream($stream, $tempStream);
        fclose($tempStream);
        fclose($stream);

        // Load the document
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempFile);

        // Helper: Replace text in all elements recursively
        $replaceInElements = function(array $elements, array $replacements) use (&$replaceInElements) {
            foreach ($elements as $element) {
                // Replace in text runs
                if (method_exists($element, 'getText') && method_exists($element, 'setText')) {
                    $text = $element->getText();
                    foreach ($replacements as $replacement) {
                        $text = str_ireplace($replacement['originalText'], $replacement['replacementText'], $text);
                    }
                    $element->setText($text);
                }
                // Replace in tables
                if (method_exists($element, 'getRows')) {
                    foreach ($element->getRows() as $row) {
                        foreach ($row->getCells() as $cell) {
                            $replaceInElements($cell->getElements(), $replacements);
                        }
                    }
                }
                // Replace in lists
                if (method_exists($element, 'getItems')) {
                    foreach ($element->getItems() as $item) {
                        $replaceInElements($item->getElements(), $replacements);
                    }
                }
                // Replace in nested elements
                if (method_exists($element, 'getElements')) {
                    $replaceInElements($element->getElements(), $replacements);
                }
            }
        };

        // Build replacements array
        $replacements = [];
        foreach ($processedEntities as $entity) {
            $replacements[] = [
                'originalText' => $entity['text'],
                'replacementText' => '[' . $entity['entityType'] . ': ' . $entity['key'] . ']'
            ];
        }

        // Replace in headers
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getHeaders() as $header) {
                $replaceInElements($header->getElements(), $replacements);
            }
        }
        // Replace in main content
        foreach ($phpWord->getSections() as $section) {
            $replaceInElements($section->getElements(), $replacements);
        }
        // Replace in footers
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getFooters() as $footer) {
                $replaceInElements($footer->getElements(), $replacements);
            }
        }

        // Save the anonymized document to a new temp file
        $anonymizedTempFile = tempnam(sys_get_temp_dir(), 'docudesk_word_anon_');
        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($anonymizedTempFile);

        // Get the parent folder and create the new file
        $parentFolder = $node->getParent();
        if ($parentFolder->nodeExists($anonymizedFileName)) {
            $parentFolder->get($anonymizedFileName)->delete();
        }
        $anonymizedStream = fopen($anonymizedTempFile, 'r');
        $newFile = $parentFolder->newFile($anonymizedFileName, $anonymizedStream);
        // Do NOT call fclose($anonymizedStream) here; Nextcloud handles the stream lifecycle internally.

        // Clean up temp files
        unlink($tempFile);
        unlink($anonymizedTempFile);

        return $newFile;
    }

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
    public function processAnonymization(\OCP\Files\Node $node, ?array $report = null)
    {
        $startTime = microtime(true);
        
        // Create a new file name with "_anonymized" suffix
        $fileName = $node->getName();
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);

        // If the file is already anonymized, skip processing and return
        if (str_ends_with($fileNameWithoutExtension, '_anonymized')) {
            $this->logger->info('Skipping anonymization for file already ending with _anonymized: ' . $fileName);
            return;
        }

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

        // Create a new file name with "_anonymized" suffix
        $fileName = $node->getName();
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $anonymizedFileName = $fileNameWithoutExtension . '_anonymized';
        if (!empty($fileExtension)) {
            $anonymizedFileName .= '.' . $fileExtension;
        }

        // If the file is already anonymized, get the existing anonymization
        if (str_ends_with($fileNameWithoutExtension, '_anonymized')) {
            $this->logger->debug('File is already anonymized, getting existing anonymization');
            $anonymization = $this->getAnonymization($node);
            if ($anonymization !== null) {
                return $anonymization;
            }
        }
        
        $this->logger->error('we found the file');

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
                'fileHash' => $report['fileHash'] ?? $fileHash,
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
            $anonymizationEntity = $this->objectService->saveObject(
                object: $anonymization, 
                register: $this->anonymizationRegisterType,
                schema: $this->anonymizationSchemaType
            );
            $anonymization = $anonymizationEntity->jsonSerialize();
            $this->logger->error('Made the anonymization object under id: ' . $anonymization['id']);
        }

        $this->logger->error('Made the anonymization object under register: ' . $this->anonymizationRegisterType);
        $this->logger->error('Made the anonymization object under schema: ' . $this->anonymizationSchemaType);
       
        // Lets return the anonymization if the hash is the same
        if ($anonymization['fileHash'] === $fileHash && $anonymization['status'] === 'completed') {
            $this->logger->debug(
                'File hash matches existing anonymization, returning cached result', [
                'fileHash' => $fileHash,
                'anonymizationId' => $anonymization['id'] ?? null
                ]
            );
            // Save the anonymization result before returning
            $anonymization['message'] = 'File hash matches existing anonymization, returning cached result';
            $this->objectService->saveObject(object: $anonymization, uuid: $anonymization['id'] ?? null);
            return $anonymization;
        }

        // Check if anonymization is needed (if there are entities)
        if (empty($report['entities'])) {
            $this->logger->info('No entities detected for anonymization in document: ' . $node->getPath());            

            // Update result array
            $anonymization['status'] = 'completed';
            $anonymization['message'] = 'No entities detected for anonymization in document: ' . $node->getPath();
            $anonymization['endTime'] = microtime(true);
            $anonymization['processingTime'] = $anonymization['endTime'] - $startTime;
            
            // Save the anonymization result before returning
            $this->objectService->saveObject(object: $anonymization, uuid: $anonymization['id'] ?? null);
            
            return $anonymization;
        }

        // Update anonymization with entities from report
        $anonymization['entities'] = $report['entities'];
        $anonymization['status'] = 'processing';
        
        // Save the updated log
        $this->objectService->saveObject(object: $anonymization, uuid: $anonymization['id'] ?? null);

        // Process entities and find their positions in the content if not provided
        $processedEntities = [];
        foreach ($report['entities'] as $entity) {
            $entityType = $entity['entityType'] ?? 'UNKNOWN';
            $entityText = $entity['text'] ?? '';
            $score = $entity['score'] ?? 0;
            if (empty($entityText)) {
                continue;
            }
            $processedEntities[] = [
                'entityType' => $entityType,
                'text' => $entityText,
                'score' => $score,
                'key' => substr(\Symfony\Component\Uid\Uuid::v4()->toRfc4122(), 0, 8)
            ];
        }

        // If the file is a Word document, anonymize using PhpWord
        if (in_array(strtolower($fileExtension), ['doc', 'docx'])) {
            $newFile = $this->anonymizeWordDocument($node, $processedEntities, $anonymizedFileName);
        } else {
            // For other file types, use the old logic
            $content = $node->getContent();
            if (empty($content)) {
                throw new Exception('Failed to get content from file: ' . $node->getPath());
            }
            $anonymizedContent = $content;
            foreach ($processedEntities as $entity) {
                $anonymizedContent = str_ireplace($entity['text'], '[' . $entity['entityType'] . ': ' . $entity['key'] . ']', $anonymizedContent);
            }
            $parentFolder = $node->getParent();
            if ($parentFolder->nodeExists($anonymizedFileName)) {
                $parentFolder->get($anonymizedFileName)->delete();
            }
            $newFile = $parentFolder->newFile($anonymizedFileName, $anonymizedContent);
        }

        // Update anonymization object
        $endTime = microtime(true);
        $anonymization['status'] = 'completed';
        $anonymization['message'] = 'Anonymization completed successfully';
        $anonymization['replacements'] = $processedEntities;
        $anonymization['anonymizedFileName'] = $anonymizedFileName;
        $anonymization['anonymizedFilePath'] = $newFile->getPath();
        $anonymization['endTime'] = $endTime;
        $anonymization['processingTime'] = $endTime - $startTime;
        
        // Save the updated log
        $anonymizationEntity = $this->objectService->saveObject(object: $anonymization, uuid: $anonymization['id'] ?? null);
        return $anonymizationEntity->jsonSerialize();
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
     * @psalm-return   array<string, mixed>|null
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
            
            $config['filters'] = [
                'nodeId' => $node->getId(),
                'register' => $this->anonymizationRegisterType,
                'schema' => $this->anonymizationSchemaType
            ];

            $anonymizations = $this->objectService->findAll($config);
            
            // Throw error if multiple anonymizations found
            if (count($anonymizations) > 1) {
                throw new \RuntimeException('Multiple anonymizations found for node ' . $node->getId() . '. There should only be one anonymization per node.');
            }
            
            return !empty($anonymizations) ? $anonymizations[0]->jsonSerialize() : null;
        } catch (Exception $e) {
            $this->logger->error(
                'Failed to retrieve anonymization: ' . $e->getMessage(), [
                'nodeId' => $node->getId(),
                'exception' => $e
                ]
            );
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
     * @psalm-return   bool
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
    }
}
