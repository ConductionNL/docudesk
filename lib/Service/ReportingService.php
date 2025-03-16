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
     * Constructor for ReportingService
     *
     * @param LoggerInterface   $logger            Logger for error reporting
     * @param IConfig           $config            Configuration service
     * @param ObjectService     $objectService     Service for storing objects
     * @param ExtractionService $extractionService Service for extracting text from documents
     *
     * @return void
     */
    public function __construct(
        LoggerInterface $logger,
        IConfig $config,
        ObjectService $objectService,
        ExtractionService $extractionService
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->objectService = $objectService;
        $this->extractionService = $extractionService;
        
        // Initialize Guzzle HTTP client
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Generate a report for a document
     *
     * This method extracts text from a document, sends it to Presidio for analysis,
     * and stores the results as a report object.
     *
     * @param string $filePath       Path to the document file
     * @param string $documentId     ID of the document (optional)
     * @param string $documentTitle  Title of the document (optional)
     * @param float  $threshold      Confidence threshold for entity detection (optional)
     *
     * @return array<string, mixed>|null The generated report or null if generation failed
     *
     * @throws Exception If report generation fails
     *
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function generateReport(
        string $filePath,
        string $documentId = '',
        string $documentTitle = '',
        float $threshold = self::DEFAULT_CONFIDENCE_THRESHOLD
    ): ?array {
        try {
            // Extract text from document
            $text = $this->extractionService->extractText($filePath);
            
            if (empty($text)) {
                $this->logger->warning('Failed to extract text from document: ' . $filePath);
                return null;
            }
            
            // Extract metadata
            $metadata = $this->extractionService->extractMetadata($filePath);
            
            // If document title is not provided, use filename from metadata
            if (empty($documentTitle) && isset($metadata['filename'])) {
                $documentTitle = $metadata['filename'];
            }
            
            // Send text to Presidio for analysis
            $presidioResults = $this->analyzeWithPresidio($text, $threshold);
            
            if (empty($presidioResults)) {
                $this->logger->info('No entities detected in document: ' . $filePath);
                // Still create a report even if no entities were found
                $presidioResults = ['entities' => []];
            }
            
            // Create report object
            $report = $this->createReportObject(
                $text,
                $presidioResults,
                $documentId,
                $documentTitle,
                $metadata
            );
            
            return $report;
        } catch (Exception $e) {
            $this->logger->error('Error generating report: ' . $e->getMessage(), ['exception' => $e]);
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
            'language' => 'en', // Default to English
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
     * Create a report object and store it
     *
     * @param string               $text          Original document text
     * @param array<string, mixed> $presidioData  Results from Presidio analysis
     * @param string               $documentId    ID of the document
     * @param string               $documentTitle Title of the document
     * @param array<string, mixed> $metadata      Document metadata
     *
     * @return array<string, mixed> The created report object
     *
     * @throws Exception If storing the report fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function createReportObject(
        string $text,
        array $presidioData,
        string $documentId,
        string $documentTitle,
        array $metadata
    ): array {
        // Generate a unique ID for the report if not provided
        $reportId = Uuid::v4()->toRfc4122();
        
        // Extract entities from Presidio data
        $entities = $presidioData['entities'] ?? [];
        
        // Count entities by type
        $entityCounts = [];
        foreach ($entities as $entity) {
            $type = $entity['entity_type'] ?? 'UNKNOWN';
            if (!isset($entityCounts[$type])) {
                $entityCounts[$type] = 0;
            }
            $entityCounts[$type]++;
        }
        
        // Calculate risk score based on number and types of entities
        $riskScore = $this->calculateRiskScore($entities);
        
        // Create report object
        $report = [
            'id' => $reportId,
            'documentId' => $documentId,
            'documentTitle' => $documentTitle,
            'createdAt' => (new DateTime())->format('c'),
            'metadata' => $metadata,
            'textLength' => strlen($text),
            'entities' => $entities,
            'entityCounts' => $entityCounts,
            'totalEntities' => count($entities),
            'riskScore' => $riskScore,
            'riskLevel' => $this->getRiskLevel($riskScore),
        ];
        
        // Store report using ObjectService
        try {
            $savedReport = $this->objectService->saveObject('report', $report);
            return is_array($savedReport) ? $savedReport : $report;
        } catch (Exception $e) {
            $this->logger->error('Failed to store report: ' . $e->getMessage(), ['exception' => $e]);
            // Return the report even if saving failed
            return $report;
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
     * Get a report by ID
     *
     * @param string $reportId ID of the report to retrieve
     *
     * @return array<string, mixed>|null The report or null if not found
     *
     * @psalm-return array<string, mixed>|null
     * @phpstan-return array<string, mixed>|null
     */
    public function getReport(string $reportId): ?array
    {
        try {
            $report = $this->objectService->getObject('report', $reportId);
            return is_array($report) ? $report : null;
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve report: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Get all reports, optionally filtered by document ID
     *
     * @param string|null $documentId Optional document ID to filter by
     * @param int|null    $limit      Maximum number of reports to return
     * @param int|null    $offset     Offset for pagination
     *
     * @return array<int, array<string, mixed>> List of reports
     *
     * @psalm-return array<int, array<string, mixed>>
     * @phpstan-return array<int, array<string, mixed>>
     */
    public function getReports(?string $documentId = null, ?int $limit = null, ?int $offset = null): array
    {
        try {
            $filters = [];
            if ($documentId !== null) {
                $filters['documentId'] = $documentId;
            }
            
            return $this->objectService->getObjects('report', $limit, $offset, $filters);
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve reports: ' . $e->getMessage(), ['exception' => $e]);
            return [];
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
} 