<?php

/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license GNU AGPL version 3 or any later version
 *
 * DocuDesk is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * DocuDesk is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DocuDesk. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later
 * @link     https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\Service;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OCP\IConfig;
use OCP\ILogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Service for generating and managing document reports
 *
 * This service handles the generation of reports based on document content,
 * including sending content to Presidio for analysis and storing the results.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later
 * @link     https://github.com/conductionnl/docudesk
 */
class ReportingService
{
    /**
     * Default Presidio API URL if not specified in configuration
     *
     * @var string
     */
    private const DEFAULT_PRESIDIO_URL = 'http://presidio-api:8080/analyze';

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
     * Object service for storing reports
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
     * Root folder service for accessing files
     *
     * @var \OCP\Files\IRootFolder
     */
    private readonly \OCP\Files\IRootFolder $rootFolder;

    /**
     * Constructor for ReportingService
     *
     * @param LoggerInterface   $logger            Logger for error reporting
     * @param IConfig           $config            Configuration service
     * @param ObjectService     $objectService     Service for storing objects
     * @param ExtractionService $extractionService Service for extracting text from documents
     * @param \OCP\Files\IRootFolder $rootFolder   Root folder service for accessing files
     *
     * @return void
     */
    public function __construct(
        LoggerInterface $logger,
        IConfig $config,
        ObjectService $objectService,
        ExtractionService $extractionService,
        \OCP\Files\IRootFolder $rootFolder
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->objectService = $objectService;
        $this->extractionService = $extractionService;
        $this->rootFolder = $rootFolder;
        
        // Initialize Guzzle HTTP client
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Process a report for a document
     *
     * This method extracts text from a document, sends it to Presidio for analysis,
     * and stores the results as a report object. It can accept either a Node or an existing report array.
     *
     * @param \OCP\Files\Node|array<string,mixed> $input Either a Node object or an existing report array
     * @param float $threshold Confidence threshold for entity detection (optional)
     *
     * @return array<string,mixed> The processed report
     *
     * @throws \InvalidArgumentException If input is invalid or node cannot be found
     * @throws Exception If report processing fails
     *
     * @psalm-return array<string,mixed>
     * @phpstan-return array<string,mixed>
     */
    public function processReport(
        \OCP\Files\Node|array $input,
        float $threshold = self::DEFAULT_CONFIDENCE_THRESHOLD
    ): array {
        try {
            // Initialize variables
            $node = null;
            $report = null;
            
            // Determine input type and get node/report
            if ($input instanceof \OCP\Files\Node) {
                $node = $input;
                $report = $this->getReport($node);
            } else if (is_array($input)) {
                $report = $input;
                $nodeId = $report['nodeId'] ?? null;
                
                if ($nodeId === null) {
                    throw new \InvalidArgumentException('Report array must contain nodeId');
                }
                
                try {
                    $node = $this->rootFolder->getById($nodeId)[0] ?? null;
                } catch (Exception $e) {
                    throw new \InvalidArgumentException('Could not find node with ID: ' . $nodeId);
                }
            } else {
                throw new \InvalidArgumentException('Input must be either Node or report array');
            }

            if (!$node || !$report) {
                throw new \InvalidArgumentException('Could not resolve both node and report from input');
            }

            // Get the report object type and set the status to processing
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            $report['status'] = 'processing';
            $report = $this->objectService->saveObject($reportObjectType, $report);

            // Extract text from document
            $filePath = $node->getPath();
            //try {
                $extraction = $this->extractionService->extractText($node);
                $text = $extraction['text'];
                $errorMessage = $extraction['errorMessage'];
            // } catch (Exception $e) {
            //     $this->logger->error('Error extracting text from document: ' . $e->getMessage(), ['exception' => $e]);
            //     $report['status'] = 'failed';
            //     $report['errorMessage'] = 'Failed to extract text from document';
            //     return $report;
            // }
            
            if (empty($text)) {
                $this->logger->warning('No text content found in document: ' . $filePath);
                $report['status'] = 'completed';
                $report['errorMessage'] = 'Document appears to be empty or contains no extractable text';
                $report['entities'] = [];
                
                // Set appropriate values for non-text documents
                $report['anonymizationResults'] = [
                    'containsPersonalData' => false,
                    'dataCategories' => [],
                    'anonymizationStatus' => 'not_required'
                ];
                
                $report['riskLevel'] = 'low';
                
                return $this->objectService->saveObject($reportObjectType, $report);
            }
                        
            // Send text to Presidio for analysis
            $report['entities'] = $this->analyzeWithPresidio($text, $threshold);
            
            if (empty($report['entities'])) {
                $this->logger->debug('No entities detected in document: ' . $filePath);
            }
            
            // Update report with results
            $report['status'] = 'completed';
            
            // Save updated report
            return $this->objectService->saveObject($reportObjectType, $report);
            
        } catch (Exception $e) {
            $this->logger->error('Error processing report: ' . $e->getMessage(), ['exception' => $e]);
            if (isset($report)) {
                $report['status'] = 'failed';
                $report['errorMessage'] = $e->getMessage();
                return $this->objectService->saveObject($reportObjectType, $report);
            }
            throw $e;
        }
    }

    /**
     * Analyze text with Presidio
     *
     * Sends text to the Presidio API for entity analysis.
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
        // Get Presidio API URL from configuration or use default
        $presidioUrl = $this->config->getSystemValue(
            'docudesk_presidio_url',
            self::DEFAULT_PRESIDIO_URL
        );
        
        // Prepare request payload
        $payload = [
            'text' => $text,
            'language' => 'nl', // Default to   @todo this should be configuration
            'score_threshold' => $threshold,
            'return_decision_process' => false,
        ];
        
        try {
            // Send request to Presidio
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
                throw new Exception('Invalid response from Presidio API');
            }
            
            return $results;
        } catch (GuzzleException $e) {
            $this->logger->error('Presidio API request failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Failed to communicate with Presidio API: ' . $e->getMessage(), 0, $e);
        }
    }


    /**
     * Calculate a risk score based on detected entities
     *
     * @param array<int, array<string, mixed>> $entities List of entities detected in the document
     *
     * @return float Risk score between 0 and 100
     *
     * @psalm-return float
     * @phpstan-return float
     */
    private function calculateRiskScore(array $entities): float
    {
        if (empty($entities)) {
            return 0.0;
        }
        
        // Define weights for different entity types
        $entityWeights = [
            'PERSON' => 5.0,
            'EMAIL_ADDRESS' => 8.0,
            'PHONE_NUMBER' => 7.0,
            'CREDIT_CARD' => 10.0,
            'IBAN_CODE' => 9.0,
            'US_SSN' => 10.0,
            'US_BANK_NUMBER' => 9.0,
            'LOCATION' => 3.0,
            'DATE_TIME' => 1.0,
            'NRP' => 8.0,
            'IP_ADDRESS' => 6.0,
            'US_DRIVER_LICENSE' => 8.0,
            'US_PASSPORT' => 9.0,
            'US_ITIN' => 9.0,
            'MEDICAL_LICENSE' => 7.0,
            'URL' => 2.0,
            'DEFAULT' => 4.0,
        ];
        
        // Calculate weighted sum of entities
        $weightedSum = 0.0;
        $totalWeight = 0.0;
        
        foreach ($entities as $entity) {
            $type = $entity['entity_type'] ?? 'DEFAULT';
            $score = $entity['score'] ?? 0.7;
            
            // Get weight for this entity type
            $weight = $entityWeights[$type] ?? $entityWeights['DEFAULT'];
            
            // Add to weighted sum
            $weightedSum += $weight * $score;
            $totalWeight += $weight;
        }
        
        // Normalize to 0-100 scale
        $baseScore = ($totalWeight > 0) ? ($weightedSum / $totalWeight) * 10 : 0;
        
        // Adjust based on number of entities (more entities = higher risk)
        $countFactor = min(count($entities) / 10, 1.0);
        
        // Final score is a combination of entity weights and count
        $finalScore = $baseScore * (1 + $countFactor);
        
        // Cap at 100
        return min($finalScore, 100.0);
    }

    /**
     * Get a risk level label based on the risk score
     *
     * @param float $riskScore Risk score between 0 and 100
     *
     * @return string Risk level label (Low, Medium, High, Critical)
     *
     * @psalm-return string
     * @phpstan-return string
     */
    private function getRiskLevel(float $riskScore): string
    {
        if ($riskScore < 20) {
            return 'Low';
        } elseif ($riskScore < 50) {
            return 'Medium';
        } elseif ($riskScore < 80) {
            return 'High';
        } else {
            return 'Critical';
        }
    }

    /**
     * Get a report for a node
     *
     * @param \OCP\Files\Node $node The file node to get the report for
     *
     * @return array<string, mixed>|null The report or null if not found
     *
     * @throws \InvalidArgumentException If the node is not a file
     * @throws \RuntimeException If multiple reports are found for the node
     *
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function getReport(\OCP\Files\Node $node): ?array
    {
        // Validate that the node is a file
        if ($node->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
            throw new \InvalidArgumentException('Node must be a file to get a report');
        }

        try {
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            
            $filters = [
                'nodeId' => $node->getId()
            ];

            $reports = $this->objectService->getObjects($reportObjectType, null, 0, $filters);
            
            // Throw error if multiple reports found
            if (count($reports) > 1) {
                throw new \RuntimeException('Multiple reports found for node ' . $node->getId() . '. There should only be one report per node.');
            }
            
            return !empty($reports) ? $reports[0] : null;
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve report: ' . $e->getMessage(), [
                'nodeId' => $node->getId(),
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Delete a report by ID
     *
     * @param string $reportId ID of the report to delete
     *
     * @return bool True if deletion was successful, false otherwise
     *
     * @psalm-return bool
     * @phpstan-return bool
     */
    public function deleteReport(string $reportId): bool
    {
        try {
            return $this->objectService->deleteObject('report', $reportId);
        } catch (Exception $e) {
            $this->logger->error('Failed to delete report: ' . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

     /**
     * Process an existing report
     *
     * @param \OCP\Files\Node $node The file node to process
     *
     * @return array<string, mixed>|null The processed report or null if processing failed
     *
     * @throws Exception If report processing fails
     * @throws \InvalidArgumentException If the node is not a file
     *
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function updateReport(\OCP\Files\Node $node): ?array
    {
        // Validate that the node is a file
        if ($node->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
            throw new \InvalidArgumentException('Node must be a file to process a report');
        }

        // Get the existing report
        $report = $this->getReport($node);

        // If no report is found, create a new report
        if (!$report) {
            return $this->createReport($node);
        }

        // Use ETag as file hash if available
        $fileHash = null;
        if (method_exists($node, 'getEtag')) {
            $fileHash = $node->getEtag();
            $this->logger->debug('Using ETag as file hash: ' . $fileHash);
        } else {
            // Fall back to calculating hash
            $fileHash = $this->calculateFileHash($node->getPath());
        }

        // If the file hash has not changed, skip the report update
        if ($fileHash === $report['fileHash']) {
            $this->logger->debug('File hash has not changed, skipping report update');
            return $report;
        }

         
        // Update the report object with new values
        $report['filePath'] = $node->getPath();
        $report['fileName'] = $node->getName();
        $report['fileType'] = $node->getMimetype();
        $report['fileExtension'] = pathinfo($node->getName(), PATHINFO_EXTENSION);
        $report['fileSize'] = $node->getSize();
        $report['status'] = 'pending'; // Reset status to pending to trigger a new report
        $report['fileHash'] = $fileHash;
        
        // Reset analysis results since we're going to reprocess
        $report['errorMessage'] = null;
        
        // Only reset these if they exist (they might be null in older reports)
        if (isset($report['anonymizationResults'])) {
            $report['anonymizationResults'] = null;
        }
        if (isset($report['wcagComplianceResults'])) {
            $report['wcagComplianceResults'] = null;
        }
        if (isset($report['languageLevelResults'])) {
            $report['languageLevelResults'] = null;
        }

        // Save the updated report
        $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
        $report = $this->objectService->saveObject($reportObjectType, $report);              

        // Process the report now if synchronous processing is enabled
        if ($this->isSynchronousProcessingEnabled()) {
            return $this->processReport($report);
        }

        return $report;
    }  
   
    
    /**
     * Calculate a hash for a file
     *
     * @param string $filePath Path to the file
     *
     * @return string The file hash
     *
     * @psalm-return string
     * @phpstan-return string
     */
    public function calculateFileHash(string $filePath): string
    {
        try {
            // For small files, use the content hash
            $fileSize = filesize($filePath);
            if ($fileSize !== false && $fileSize < 10 * 1024 * 1024) { // 10 MB
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    $hash = md5($content);
                    $this->logger->debug('Calculated content hash for file', [
                        'filePath' => $filePath,
                        'hash' => $hash,
                        'method' => 'content'
                    ]);
                    return $hash;
                }
            }
            
            // For larger files, use a combination of metadata
            $stats = stat($filePath);
            if ($stats !== false) {
                $hash = md5(
                    $filePath . 
                    $stats['size'] . 
                    $stats['mtime']
                );
                $this->logger->debug('Calculated metadata hash for file', [
                    'filePath' => $filePath,
                    'hash' => $hash,
                    'method' => 'metadata'
                ]);
                return $hash;
            }
            
            // Fallback to just the path
            $hash = md5($filePath);
            $this->logger->debug('Calculated fallback hash for file', [
                'filePath' => $filePath,
                'hash' => $hash,
                'method' => 'path'
            ]);
            return $hash;
        } catch (Exception $e) {
            $this->logger->warning('Failed to calculate file hash: ' . $e->getMessage(), [
                'filePath' => $filePath,
                'exception' => $e
            ]);
            
            // Fallback to just the path
            $hash = md5($filePath);
            $this->logger->debug('Calculated fallback hash after error', [
                'filePath' => $filePath,
                'hash' => $hash,
                'method' => 'path'
            ]);
            return $hash;
        }
    }
    
    /**
     * Process pending reports
     *
     * @param int $limit Maximum number of reports to process
     *
     * @return int Number of reports processed
     *
     * @psalm-return int
     * @phpstan-return int
     */
    public function processPendingReports(int $limit = 10): int
    {
        // Check if reporting is enabled
        if (!$this->isReportingEnabled()) {
            $this->logger->debug('Reporting is disabled, skipping processing of pending reports');
            return 0;
        }

        try {
            // Find pending reports
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            $filters = [
                'status' => 'pending',
            ];
            
            $pendingReports = $this->objectService->getObjects(
                $reportObjectType,
                $limit,
                0,
                $filters
            );
            
            if (empty($pendingReports)) {
                $this->logger->debug('No pending reports found');
                return 0;
            }
            
            $this->logger->info('Processing ' . count($pendingReports) . ' pending reports');
            
            $processedCount = 0;
            foreach ($pendingReports as $report) {
                try {
                    $nodeId = $report['nodeId'] ?? null;
                    $filePath = $report['filePath'] ?? null;
                    $fileName = $report['fileName'] ?? null;
                    
                    if ($nodeId === null) {
                        $this->logger->warning('Report has no nodeId, marking as failed', [
                            'reportId' => $report['id'] ?? 'unknown'
                        ]);
                        
                        $report['status'] = 'failed';
                        $report['errorMessage'] = 'Missing nodeId';
                        $this->objectService->saveObject($reportObjectType, $report);
                        continue;
                    }
                    
                    // Process the report
                    $this->processReport($report);
                    $processedCount++;
                } catch (Exception $e) {
                    $this->logger->error('Error processing report: ' . $e->getMessage(), [
                        'reportId' => $report['id'] ?? 'unknown',
                        'exception' => $e
                    ]);
                }
            }
            
            return $processedCount;
        } catch (Exception $e) {
            $this->logger->error('Error processing pending reports: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return 0;
        }
    }
    
    /**
     * Check if reporting is enabled
     *
     * @return bool True if reporting is enabled, false otherwise
     *
     * @psalm-return bool
     * @phpstan-return bool
     */
    public function isReportingEnabled(): bool
    {
        return $this->config->getSystemValue('docudesk_enable_reporting', true);
    }
    
    /**
     * Check if synchronous processing is enabled
     *
     * @return bool True if synchronous processing is enabled, false otherwise
     *
     * @psalm-return bool
     * @phpstan-return bool
     */
    public function isSynchronousProcessingEnabled(): bool
    {
        return $this->config->getSystemValue('docudesk_synchronous_processing', false);
    }

    /**
     * Create a report from a Nextcloud node
     *
     * @param \OCP\Files\Node $node The file node
     *
     * @return array<string, mixed>|null The created report or null if creation failed
     *
     * @throws \InvalidArgumentException If the node is not a file
     * @throws Exception If report creation fails
     *
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function createReport(\OCP\Files\Node $node): ?array
    {
        // Validate that the node is a file
        if ($node->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
            throw new \InvalidArgumentException('Node must be a file to create a report');
        }
        
        // Check if reporting is enabled
        if (!$this->isReportingEnabled()) {
            $this->logger->debug('Reporting is disabled, skipping report creation for node: ' . $node->getId());
            return null;
        }      
        
        // Check if a report already exists for this node and return updated report if found
        if ($existingReport = $this->getReport($node)) {
            $this->logger->debug('Report already exists for node: ' . $node->getId() . ' with hash: ' . $existingReport['fileHash']);
            return $this->updateReport($node);
        }
        
        // Lets setup the report object with all fields from the documentation
        $report = [
            'nodeId' => $node->getId(),
            'filePath' => $node->getPath(),
            'fileName' => $node->getName(),
            'fileType' => $node->getMimetype(),
            'fileExtension' => pathinfo($node->getName(), PATHINFO_EXTENSION),
            'fileSize' => $node->getSize(),
            'status' => 'pending',
            'errorMessage' => null,
            'riskScore' => null, // Will be calculated during processing
            'riskLevel' => 'unknown', // Default value, will be updated during processing
            'anonymizationResults' => [], // Will be populated during processing
            'entities' => [], // Will be populated during processing
            'wcagComplianceResults' => [], // Will be populated if WCAG analysis is enabled
            'languageLevelResults' => [], // Will be populated if language level analysis is enabled
            'retentionPeriod' => 0, // Default to indefinite retention
            'retentionExpiry' => null,
            'legalBasis' => null,
            'dataController' => null,
        ];

        // Use ETag as file hash if available
        if (method_exists($node, 'getEtag')) {
            $report['fileHash'] = $node->getEtag();
            $this->logger->debug('Using ETag as file hash: ' . $report['fileHash']);
        } else {
            // Fall back to calculating hash
            $report['fileHash'] = $this->calculateFileHash($node->getPath());
        }
        
        // Save the report
        $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
        $report = $this->objectService->saveObject($reportObjectType, $report);        

        // Process the report now if synchronous processing is enabled
        if ($this->isSynchronousProcessingEnabled()) {
            return $this->processReport($report);
        }

        return $report;
    }

    /**
     * Check if anonymization is enabled
     *
     * @return bool True if anonymization is enabled, false otherwise
     *
     * @psalm-return bool
     * @phpstan-return bool
     */
    public function isAnonymizationEnabled(): bool
    {
        return $this->config->getSystemValue('docudesk_enable_anonymization', true);
    }
} 