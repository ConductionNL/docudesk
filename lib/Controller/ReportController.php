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
 * @category Controller
 * @package  OCA\DocuDesk\Controller
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\Controller;

use OCA\DocuDesk\Service\ObjectService;
use OCA\DocuDesk\Service\ReportingService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Controller for managing document reports
 *
 * This controller provides endpoints for creating, retrieving, and managing
 * document reports.
 *
 * @category Controller
 * @package  OCA\DocuDesk\Controller
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */
class ReportController extends Controller
{
    /**
     * Constructor for ReportController
     *
     * @param string           $appName          The app name
     * @param IRequest         $request          The request object
     * @param ObjectService    $objectService    Service for handling objects
     * @param ReportingService $reportingService Service for generating reports
     * @param IRootFolder      $rootFolder       Root folder service
     * @param IUserSession     $userSession      User session service
     * @param IConfig          $config           Configuration service
     * @param LoggerInterface  $logger           Logger for error reporting
     *
     * @return void
     */
    public function __construct(
        string $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly ReportingService $reportingService,
        private readonly IRootFolder $rootFolder,
        private readonly IUserSession $userSession,
        private readonly IConfig $config,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * Get a list of reports
     *
     * @param int|null    $limit   Maximum number of reports to return
     * @param int|null    $offset  Offset for pagination
     * @param string|null $nodeId  Filter reports by node ID
     * @param string|null $status  Filter reports by status
     * @param string|null $orderBy Order reports by field
     * @param string|null $order   Order direction (asc or desc)
     *
     * @return JSONResponse JSON response containing the reports
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @psalm-return JSONResponse
     * @phpstan-return JSONResponse
     */
    public function index(
        ?int $limit = null,
        ?int $offset = null,
        ?string $nodeId = null,
        ?string $status = null,
        ?string $orderBy = null,
        ?string $order = null
    ): JSONResponse {
        try {
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            
            $filters = [];
            if ($nodeId !== null) {
                $filters['node_id'] = $nodeId;
            }
            if ($status !== null) {
                $filters['status'] = $status;
            }
            
            // Get the current user
            $user = $this->userSession->getUser();
            if ($user === null) {
                return new JSONResponse(['error' => 'User not authenticated'], Http::STATUS_UNAUTHORIZED);
            }
            
            // Filter reports by user ID
            $filters['user_id'] = $user->getUID();
            
            $reports = $this->objectService->getObjects($reportObjectType, $limit, $offset, $filters, $orderBy, $order);
            
            return new JSONResponse($reports);
        } catch (\Exception $e) {
            $this->logger->error('Error getting reports: ' . $e->getMessage(), ['exception' => $e]);
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a specific report by ID
     *
     * @param string $id The report ID
     *
     * @return JSONResponse JSON response containing the report
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @psalm-return JSONResponse
     * @phpstan-return JSONResponse
     */
    public function show(string $id): JSONResponse
    {
        try {
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            
            $report = $this->objectService->getObject($reportObjectType, $id);
            
            if ($report === null) {
                return new JSONResponse(['error' => 'Report not found'], Http::STATUS_NOT_FOUND);
            }
            
            // Check if the report belongs to the current user
            $user = $this->userSession->getUser();
            if ($user === null) {
                return new JSONResponse(['error' => 'User not authenticated'], Http::STATUS_UNAUTHORIZED);
            }
            
            if ($report['user_id'] !== $user->getUID()) {
                return new JSONResponse(['error' => 'Access denied'], Http::STATUS_FORBIDDEN);
            }
            
            return new JSONResponse($report);
        } catch (\Exception $e) {
            $this->logger->error('Error getting report: ' . $e->getMessage(), ['exception' => $e]);
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new report
     *
     * @param int|null    $nodeId       The node ID of the file
     * @param string|null $fileName     The name of the file
     * @param string|null $filePath     The path of the file
     * @param bool|null   $processNow   Whether to process the report immediately
     * @param array|null  $analysisTypes The types of analysis to perform
     *
     * @return JSONResponse JSON response containing the created report
     *
     * @NoCSRFRequired
     *
     * @psalm-return JSONResponse
     * @phpstan-return JSONResponse
     */
    public function create(
        ?int $nodeId = null,
        ?string $fileName = null,
        ?string $filePath = null,
        ?bool $processNow = false,
        ?array $analysisTypes = null
    ): JSONResponse {
        try {
            // Validate required parameters
            if ($nodeId === null) {
                return new JSONResponse(['error' => 'Node ID is required'], 400);
            }
            
            // Get the current user
            $user = $this->userSession->getUser();
            if ($user === null) {
                return new JSONResponse(['error' => 'User not authenticated'], 401);
            }
            
            $userId = $user->getUID();
            
            // Get the file node
            $userFolder = $this->rootFolder->getUserFolder($userId);
            $nodes = $userFolder->getById($nodeId);
            
            if (empty($nodes)) {
                return new JSONResponse(['error' => 'File not found'], 404);
            }
            
            $node = $nodes[0];
            
            // Use file properties if not provided
            $fileName = $fileName ?? $node->getName();
            $filePath = $filePath ?? $node->getPath();
            
            // Create the report using the ReportingService
            $report = $this->reportingService->createReport(
                filePath: $filePath,
                nodeId: $nodeId,
                fileName: $fileName,
                userId: $userId,
                processNow: $processNow ?? false
            );
            
            if ($report === null) {
                return new JSONResponse(['error' => 'Failed to create report'], 500);
            }
            
            return new JSONResponse($report);
        } catch (\Exception $e) {
            $this->logger->error('Error creating report: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a report
     *
     * @param string $id      The report ID
     * @param array  $updates The updates to apply
     *
     * @return JSONResponse JSON response containing the updated report
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @psalm-return JSONResponse
     * @phpstan-return JSONResponse
     */
    public function update(string $id, array $updates): JSONResponse
    {
        try {
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            
            $report = $this->objectService->getObject($reportObjectType, $id);
            
            if ($report === null) {
                return new JSONResponse(['error' => 'Report not found'], Http::STATUS_NOT_FOUND);
            }
            
            // Check if the report belongs to the current user
            $user = $this->userSession->getUser();
            if ($user === null) {
                return new JSONResponse(['error' => 'User not authenticated'], Http::STATUS_UNAUTHORIZED);
            }
            
            if ($report['user_id'] !== $user->getUID()) {
                return new JSONResponse(['error' => 'Access denied'], Http::STATUS_FORBIDDEN);
            }
            
            // Apply updates
            foreach ($updates as $key => $value) {
                // Prevent updating certain fields
                if (in_array($key, ['id', 'node_id', 'file_hash', 'user_id', 'created_at'])) {
                    continue;
                }
                
                $report[$key] = $value;
            }
            
            // Update the updated_at timestamp
            $report['updated_at'] = time();
            
            $updatedReport = $this->objectService->saveObject($reportObjectType, $report);
            
            return new JSONResponse($updatedReport);
        } catch (\Exception $e) {
            $this->logger->error('Error updating report: ' . $e->getMessage(), ['exception' => $e]);
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a report
     *
     * @param string $id The report ID
     *
     * @return JSONResponse JSON response indicating success or failure
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @psalm-return JSONResponse
     * @phpstan-return JSONResponse
     */
    public function destroy(string $id): JSONResponse
    {
        try {
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            
            $report = $this->objectService->getObject($reportObjectType, $id);
            
            if ($report === null) {
                return new JSONResponse(['error' => 'Report not found'], Http::STATUS_NOT_FOUND);
            }
            
            // Check if the report belongs to the current user
            $user = $this->userSession->getUser();
            if ($user === null) {
                return new JSONResponse(['error' => 'User not authenticated'], Http::STATUS_UNAUTHORIZED);
            }
            
            if ($report['user_id'] !== $user->getUID()) {
                return new JSONResponse(['error' => 'Access denied'], Http::STATUS_FORBIDDEN);
            }
            
            $success = $this->objectService->deleteObject($reportObjectType, $id);
            
            if ($success) {
                return new JSONResponse(['success' => true]);
            } else {
                return new JSONResponse(['error' => 'Failed to delete report'], Http::STATUS_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error deleting report: ' . $e->getMessage(), ['exception' => $e]);
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the latest report for a node
     *
     * @param int $nodeId The node ID
     *
     * @return JSONResponse JSON response containing the latest report
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @psalm-return JSONResponse
     * @phpstan-return JSONResponse
     */
    public function getLatestForNode(int $nodeId): JSONResponse
    {
        try {
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            
            // Get the current user
            $user = $this->userSession->getUser();
            if ($user === null) {
                return new JSONResponse(['error' => 'User not authenticated'], Http::STATUS_UNAUTHORIZED);
            }
            
            $filters = [
                'node_id' => $nodeId,
                'user_id' => $user->getUID(),
            ];
            
            $reports = $this->objectService->getObjects($reportObjectType, 1, 0, $filters, 'created_at', 'desc');
            
            if (empty($reports)) {
                return new JSONResponse(['error' => 'No reports found for this node'], Http::STATUS_NOT_FOUND);
            }
            
            return new JSONResponse($reports[0]);
        } catch (\Exception $e) {
            $this->logger->error('Error getting latest report for node: ' . $e->getMessage(), ['exception' => $e]);
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Process a report
     *
     * @param string $id The ID of the report to process
     *
     * @return JSONResponse JSON response containing the processed report
     *
     * @NoCSRFRequired
     *
     * @psalm-return JSONResponse
     * @phpstan-return JSONResponse
     */
    public function process(string $id): JSONResponse
    {
        try {
            // Get the report
            $reportObjectType = $this->config->getSystemValue('docudesk_report_object_type', 'report');
            $report = $this->objectService->getObject($reportObjectType, $id);
            
            if ($report === null) {
                return new JSONResponse(['error' => 'Report not found'], 404);
            }
            
            // Get the node ID
            $nodeId = $report['node_id'] ?? null;
            if ($nodeId === null) {
                return new JSONResponse(['error' => 'Report has no node ID'], 400);
            }
            
            // Get the file path
            $filePath = $report['file_path'] ?? null;
            $fileName = $report['file_name'] ?? null;
            
            if ($filePath === null || $fileName === null) {
                // Try to get the file node
                $user = $this->userSession->getUser();
                if ($user === null) {
                    return new JSONResponse(['error' => 'User not authenticated'], 401);
                }
                
                $userFolder = $this->rootFolder->getUserFolder($user->getUID());
                $nodes = $userFolder->getById($nodeId);
                
                if (empty($nodes)) {
                    return new JSONResponse(['error' => 'File not found'], 404);
                }
                
                $node = $nodes[0];
                $filePath = $node->getPath();
                $fileName = $node->getName();
                
                // Update the report with the file path and name
                $report['file_path'] = $filePath;
                $report['file_name'] = $fileName;
                $this->objectService->saveObject($reportObjectType, $report);
            }
            
            // Process the report
            $processedReport = $this->reportingService->processExistingReport($report, $filePath, $fileName);
            
            if ($processedReport === null) {
                return new JSONResponse(['error' => 'Failed to process report'], 500);
            }
            
            return new JSONResponse($processedReport);
        } catch (\Exception $e) {
            $this->logger->error('Error processing report: ' . $e->getMessage(), [
                'report_id' => $id,
                'exception' => $e
            ]);
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
} 