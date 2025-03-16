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
        IUserSession $userSession
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->objectService = $objectService;
        $this->extractionService = $extractionService;
        $this->userSession = $userSession;
        
        // Initialize Guzzle HTTP client
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Anonymize a document
     *
     * This method extracts text from a document, detects sensitive information using Presidio,
     * anonymizes the text, and stores the anonymization data.
     *
     * @param string $filePath                Path to the document file
     * @param string $outputPath              Path where the anonymized document should be saved
     * @param string $documentId              ID of the document (optional)
     * @param string $documentTitle           Title of the document (optional)
     * @param float  $threshold               Confidence threshold for entity detection (optional)
     * @param array  $anonymizationOperators  Custom anonymization operators (optional)
     * @param bool   $storeOriginalText       Whether to store the original text for de-anonymization (optional)
     *
     * @return array<string, mixed> The anonymization result
     *
     * @throws Exception If anonymization fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    public function anonymizeDocument(
        string $filePath,
        string $outputPath,
        string $documentId = '',
        string $documentTitle = '',
        float $threshold = self::DEFAULT_CONFIDENCE_THRESHOLD,
        array $anonymizationOperators = [],
        bool $storeOriginalText = true
    ): array {
        $startTime = microtime(true);
        
        try {
            // Extract text from document
            $originalText = $this->extractionService->extractText($filePath);
            
            if (empty($originalText)) {
                throw new Exception('Failed to extract text from document: ' . $filePath);
            }
            
            // Extract metadata
            $metadata = $this->extractionService->extractMetadata($filePath);
            
            // If document title is not provided, use filename from metadata
            if (empty($documentTitle) && isset($metadata['filename'])) {
                $documentTitle = $metadata['filename'];
            }
            
            // Detect entities using Presidio
            $analysisResults = $this->analyzeWithPresidio($originalText, $threshold);
            
            if (empty($analysisResults) || empty($analysisResults['entities'])) {
                $this->logger->info('No entities detected for anonymization in document: ' . $filePath);
                // Return early with empty result
                return $this->createAnonymizationResult(
                    $documentId,
                    $documentTitle,
                    $originalText,
                    $originalText,
                    [],
                    [],
                    $startTime,
                    'No entities detected for anonymization'
                );
            }
            
            // Anonymize text using Presidio
            $anonymizationResult = $this->anonymizeWithPresidio(
                $originalText,
                $analysisResults,
                $anonymizationOperators
            );
            
            if (empty($anonymizationResult) || !isset($anonymizationResult['text'])) {
                throw new Exception('Failed to anonymize text');
            }
            
            $anonymizedText = $anonymizationResult['text'];
            
            // Generate a unique anonymization key for de-anonymization
            $anonymizationKey = $this->generateAnonymizationKey();
            
            // Create anonymization log
            $log = $this->createAnonymizationLog(
                $documentId,
                $documentTitle,
                $originalText,
                $anonymizedText,
                $analysisResults['entities'],
                $anonymizationResult['items'] ?? [],
                $anonymizationKey,
                $storeOriginalText,
                $startTime
            );
            
            // Write anonymized text to output file
            $this->writeAnonymizedDocument($filePath, $outputPath, $anonymizedText);
            
            return $log;
        } catch (Exception $e) {
            $this->logger->error('Error anonymizing document: ' . $e->getMessage(), ['exception' => $e]);
            
            // Create error log
            $errorLog = $this->createAnonymizationResult(
                $documentId,
                $documentTitle,
                '',
                '',
                [],
                [],
                $startTime,
                $e->getMessage()
            );
            
            // Store error log
            $this->objectService->saveObject('anonymization', $errorLog);
            
            throw $e;
        }
    }

    /**
     * Analyze text with Presidio
     *
     * Sends text to the Presidio Analyzer API for entity detection.
     *
     * @param string $text      Text to analyze
     * @param float  $threshold Confidence threshold for entity detection
     *
     * @return array<string, mixed> Presidio analysis results
     *
     * @throws Exception If the API request fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function analyzeWithPresidio(string $text, float $threshold = self::DEFAULT_CONFIDENCE_THRESHOLD): array
    {
        // Get Presidio Analyzer API URL from configuration or use default
        $presidioUrl = $this->config->getSystemValue(
            'docudesk_presidio_analyzer_url',
            self::DEFAULT_PRESIDIO_ANALYZER_URL
        );
        
        // Prepare request payload
        $payload = [
            'text' => $text,
            'language' => 'en', // Default to English
            'score_threshold' => $threshold,
            'return_decision_process' => false,
        ];
        
        try {
            // Send request to Presidio Analyzer
            $response = $this->client->post($presidioUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);
            
            // Parse response
            $responseBody = $response->getBody()->getContents();
            $results = json_decode($responseBody, true);
            
            if (!is_array($results)) {
                throw new Exception('Invalid response from Presidio Analyzer API');
            }
            
            return $results;
        } catch (GuzzleException $e) {
            $this->logger->error('Presidio Analyzer API request failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Failed to communicate with Presidio Analyzer API: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Anonymize text with Presidio
     *
     * Sends text and analysis results to the Presidio Anonymizer API for anonymization.
     *
     * @param string               $text                  Text to anonymize
     * @param array<string, mixed> $analysisResults       Results from Presidio Analyzer
     * @param array<string, mixed> $anonymizationOperators Custom anonymization operators
     *
     * @return array<string, mixed> Presidio anonymization results
     *
     * @throws Exception If the API request fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function anonymizeWithPresidio(
        string $text,
        array $analysisResults,
        array $anonymizationOperators = []
    ): array {
        // Get Presidio Anonymizer API URL from configuration or use default
        $presidioUrl = $this->config->getSystemValue(
            'docudesk_presidio_anonymizer_url',
            self::DEFAULT_PRESIDIO_ANONYMIZER_URL
        );
        
        // Default anonymization operators if none provided
        if (empty($anonymizationOperators)) {
            $anonymizationOperators = $this->getDefaultAnonymizationOperators();
        }
        
        // Prepare request payload
        $payload = [
            'text' => $text,
            'analyzer_results' => $analysisResults['entities'],
            'anonymizers' => $anonymizationOperators,
        ];
        
        try {
            // Send request to Presidio Anonymizer
            $response = $this->client->post($presidioUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);
            
            // Parse response
            $responseBody = $response->getBody()->getContents();
            $results = json_decode($responseBody, true);
            
            if (!is_array($results)) {
                throw new Exception('Invalid response from Presidio Anonymizer API');
            }
            
            return $results;
        } catch (GuzzleException $e) {
            $this->logger->error('Presidio Anonymizer API request failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Failed to communicate with Presidio Anonymizer API: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get default anonymization operators
     *
     * Returns the default anonymization operators for different entity types.
     *
     * @return array<string, mixed> Default anonymization operators
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function getDefaultAnonymizationOperators(): array
    {
        return [
            'DEFAULT' => [
                'type' => 'replace',
                'new_value' => '[REDACTED]',
            ],
            'PERSON' => [
                'type' => 'replace',
                'new_value' => '[PERSON]',
            ],
            'EMAIL_ADDRESS' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 'all',
                'from_end' => false,
            ],
            'PHONE_NUMBER' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 6,
                'from_end' => false,
            ],
            'CREDIT_CARD' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 12,
                'from_end' => true,
            ],
            'LOCATION' => [
                'type' => 'replace',
                'new_value' => '[LOCATION]',
            ],
            'DATE_TIME' => [
                'type' => 'replace',
                'new_value' => '[DATE]',
            ],
            'US_SSN' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 'all',
                'from_end' => false,
            ],
            'US_BANK_NUMBER' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 'all',
                'from_end' => false,
            ],
            'US_DRIVER_LICENSE' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 'all',
                'from_end' => false,
            ],
            'US_PASSPORT' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 'all',
                'from_end' => false,
            ],
            'IP_ADDRESS' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 'all',
                'from_end' => false,
            ],
            'NRP' => [
                'type' => 'mask',
                'masking_char' => '*',
                'chars_to_mask' => 'all',
                'from_end' => false,
            ],
        ];
    }

    /**
     * Generate a unique anonymization key
     *
     * @return string Unique anonymization key
     *
     * @psalm-return string
     * @phpstan-return string
     */
    private function generateAnonymizationKey(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    /**
     * Create an anonymization log
     *
     * @param string                        $documentId        ID of the document
     * @param string                        $documentTitle     Title of the document
     * @param string                        $originalText      Original document text
     * @param string                        $anonymizedText    Anonymized document text
     * @param array<int, array<string, mixed>> $entities       Entities detected in the document
     * @param array<int, array<string, mixed>> $replacements   Replacements made during anonymization
     * @param string                        $anonymizationKey  Key for de-anonymization
     * @param bool                          $storeOriginalText Whether to store the original text
     * @param float                         $startTime         Start time of anonymization process
     *
     * @return array<string, mixed> The created anonymization log
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function createAnonymizationLog(
        string $documentId,
        string $documentTitle,
        string $originalText,
        string $anonymizedText,
        array $entities,
        array $replacements,
        string $anonymizationKey,
        bool $storeOriginalText,
        float $startTime
    ): array {
        // Create anonymization result
        $log = $this->createAnonymizationResult(
            $documentId,
            $documentTitle,
            $storeOriginalText ? $originalText : '',
            $anonymizedText,
            $entities,
            $replacements,
            $startTime
        );
        
        // Add anonymization key
        $log['anonymizationKey'] = $anonymizationKey;
        
        // Store log using ObjectService
        try {
            $savedLog = $this->objectService->saveObject('anonymization', $log);
            return is_array($savedLog) ? $savedLog : $log;
        } catch (Exception $e) {
            $this->logger->error('Failed to store anonymization log: ' . $e->getMessage(), ['exception' => $e]);
            // Return the log even if saving failed
            return $log;
        }
    }

    /**
     * Create an anonymization result object
     *
     * @param string                        $documentId     ID of the document
     * @param string                        $documentTitle  Title of the document
     * @param string                        $originalText   Original document text
     * @param string                        $anonymizedText Anonymized document text
     * @param array<int, array<string, mixed>> $entities    Entities detected in the document
     * @param array<int, array<string, mixed>> $replacements Replacements made during anonymization
     * @param float                         $startTime      Start time of anonymization process
     * @param string                        $errorMessage   Error message if anonymization failed
     *
     * @return array<string, mixed> Anonymization result object
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function createAnonymizationResult(
        string $documentId,
        string $documentTitle,
        string $originalText,
        string $anonymizedText,
        array $entities,
        array $replacements,
        float $startTime,
        string $errorMessage = ''
    ): array {
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000); // Duration in milliseconds
        
        // Get current user ID
        $userId = '';
        $user = $this->userSession->getUser();
        if ($user !== null) {
            $userId = $user->getUID();
        }
        
        // Count entities by type
        $entityCounts = [];
        foreach ($entities as $entity) {
            $type = $entity['entity_type'] ?? 'UNKNOWN';
            if (!isset($entityCounts[$type])) {
                $entityCounts[$type] = 0;
            }
            $entityCounts[$type]++;
        }
        
        // Generate a unique ID for the log
        $logId = Uuid::v4()->toRfc4122();
        
        // Create log object
        return [
            'id' => $logId,
            'documentId' => $documentId,
            'documentTitle' => $documentTitle,
            'status' => empty($errorMessage) ? 'success' : 'error',
            'startTime' => (new DateTime('@' . intval($startTime)))->format('c'),
            'endTime' => (new DateTime('@' . intval($endTime)))->format('c'),
            'duration' => $duration,
            'errorMessage' => $errorMessage,
            'userId' => $userId,
            'originalText' => $originalText,
            'anonymizedText' => $anonymizedText,
            'entities' => $entities,
            'entityCounts' => $entityCounts,
            'totalEntities' => count($entities),
            'replacements' => $replacements,
            'totalReplacements' => count($replacements),
            'createdAt' => (new DateTime())->format('c'),
        ];
    }

    /**
     * Write anonymized text to a document
     *
     * @param string $inputPath  Path to the original document
     * @param string $outputPath Path where the anonymized document should be saved
     * @param string $anonymizedText Anonymized text content
     *
     * @return bool True if successful, false otherwise
     *
     * @throws Exception If writing the document fails
     *
     * @psalm-return bool
     * @phpstan-return bool
     */
    private function writeAnonymizedDocument(string $inputPath, string $outputPath, string $anonymizedText): bool
    {
        // Get file extension
        $extension = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
        
        // For now, just write the anonymized text to a text file
        // In a real implementation, this would need to handle different file formats
        // and preserve formatting
        
        // For simplicity, we'll just write the text to the output file
        $result = file_put_contents($outputPath, $anonymizedText);
        
        if ($result === false) {
            throw new Exception('Failed to write anonymized document to: ' . $outputPath);
        }
        
        return true;
    }

    /**
     * De-anonymize a document
     *
     * @param string $anonymizationId ID of the anonymization log
     * @param string $outputPath      Path where the de-anonymized document should be saved
     *
     * @return array<string, mixed> The de-anonymization result
     *
     * @throws Exception If de-anonymization fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    public function deanonymizeDocument(string $anonymizationId, string $outputPath): array
    {
        try {
            // Get anonymization log
            $log = $this->objectService->getObject('anonymization', $anonymizationId);
            
            if (!is_array($log)) {
                throw new Exception('Anonymization log not found: ' . $anonymizationId);
            }
            
            // Check if original text is available
            if (empty($log['originalText'])) {
                throw new Exception('Original text not available for de-anonymization');
            }
            
            // Write original text to output file
            $result = file_put_contents($outputPath, $log['originalText']);
            
            if ($result === false) {
                throw new Exception('Failed to write de-anonymized document to: ' . $outputPath);
            }
            
            // Create de-anonymization result
            return [
                'success' => true,
                'documentId' => $log['documentId'] ?? '',
                'documentTitle' => $log['documentTitle'] ?? '',
                'outputPath' => $outputPath,
            ];
        } catch (Exception $e) {
            $this->logger->error('Error de-anonymizing document: ' . $e->getMessage(), ['exception' => $e]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get an anonymization log by ID
     *
     * @param string $logId ID of the anonymization log to retrieve
     *
     * @return array<string, mixed>|null The anonymization log or null if not found
     *
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function getAnonymizationLog(string $logId): ?array
    {
        try {
            $log = $this->objectService->getObject('anonymization', $logId);
            return is_array($log) ? $log : null;
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve anonymization log: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Get all anonymization logs, optionally filtered by document ID
     *
     * @param string|null $documentId Optional document ID to filter by
     * @param int|null    $limit      Maximum number of logs to return
     * @param int|null    $offset     Offset for pagination
     *
     * @return array<int, array<string, mixed>> List of anonymization logs
     *
     * @psalm-return array<int, array<string, mixed>>
     * @phpstan-return array<int, array<string, mixed>>
     */
    public function getAnonymizationLogs(?string $documentId = null, ?int $limit = null, ?int $offset = null): array
    {
        try {
            $filters = [];
            if ($documentId !== null) {
                $filters['documentId'] = $documentId;
            }
            
            return $this->objectService->getObjects('anonymization', $limit, $offset, $filters);
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve anonymization logs: ' . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }

    /**
     * Delete an anonymization log by ID
     *
     * @param string $logId ID of the anonymization log to delete
     *
     * @return bool True if deletion was successful, false otherwise
     *
     * @psalm-return bool
     * @phpstan-return bool
     */
    public function deleteAnonymizationLog(string $logId): bool
    {
        try {
            return $this->objectService->deleteObject('anonymization', $logId);
        } catch (Exception $e) {
            $this->logger->error('Failed to delete anonymization log: ' . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
