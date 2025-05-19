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
 * @category EventListener
 * @package  OCA\DocuDesk\EventListener
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\EventListener;

use InvalidArgumentException;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\AbstractNodeEvent;
use OCP\Files\Events\Node\NodeCreatedEvent;
use OCP\Files\Events\Node\NodeDeletedEvent;
use OCP\Files\Events\Node\NodeTouchedEvent;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\FileInfo;
use OCA\DocuDesk\Service\ReportingService;
use Psr\Log\LoggerInterface;

/**
 * Event listener for file-related node events
 *
 * This class listens for events related to file operations in Nextcloud
 * and handles them appropriately, including triggering report creation.
 *
 * @category EventListener
 * @package  OCA\DocuDesk\EventListener
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2
 * @link     https://github.com/conductionnl/docudesk
 */
class FileEventListener implements IEventListener
{


    /**
     * Constructor for FileEventListener
     *
     * @param ReportingService $reportingService Service for generating reports
     * @param LoggerInterface  $logger           Logger for error reporting
     *
     * @return void
     */
    public function __construct(
        private readonly ReportingService $_reportingService,
        private readonly LoggerInterface $_logger
    ) {

    }//end __construct()


    /**
     * Handle the event
     *
     * @param Event $event The event to handle
     *
     * @return void
     *
     * @throws InvalidArgumentException If the event type is unsupported
     */
    public function handle(Event $event): void
    {
        if (!($event instanceof AbstractNodeEvent)) {
            return;
        }

        $node = $event->getNode();

        // Only process file events, not folder events
        if ($node->getType() !== FileInfo::TYPE_FILE) {
            return;
        }

        try {
            match (true) {
                $event instanceof NodeCreatedEvent => $this->_handleNodeCreated($event),
                $event instanceof NodeDeletedEvent => $this->_handleNodeDeleted($event),
                $event instanceof NodeTouchedEvent => $this->_handleNodeTouched($event),
                $event instanceof NodeWrittenEvent => $this->_handleNodeWritten($event),
            default => throw new InvalidArgumentException('Unsupported event type: '.get_class($event)),
            };
        } catch (\Exception $e) {
            $this->_logger->error(
                    'Error handling file event: '.$e->getMessage(),
                    [
                        'event'     => get_class($event),
                        'node_id'   => $node->getId(),
                        'exception' => $e,
                    ]
                    );
        }

    }//end handle()


    /**
     * Handle node created event
     *
     * @param NodeCreatedEvent $event The node created event
     *
     * @return void
     */
    private function _handleNodeCreated(NodeCreatedEvent $event): void
    {
        $node = $event->getNode();
        $this->_logger->debug(
                'File created: '.$node->getName(),
                [
                    'node_id' => $node->getId(),
                    'path'    => $node->getPath(),
                ]
                );

        // Always try to create a report, the ReportingService will check if reporting is enabled
        $this->_reportingService->createReport($node);

    }//end _handleNodeCreated()


    /**
     * Handle node written event
     *
     * @param NodeWrittenEvent $event The node written event
     *
     * @return void
     */
    private function _handleNodeWritten(NodeWrittenEvent $event): void
    {
        $node = $event->getNode();
        $this->_logger->debug(
                'File written: '.$node->getName(),
                [
                    'node_id' => $node->getId(),
                    'path'    => $node->getPath(),
                ]
                );

        // Get existing report
        $report = $this->_reportingService->getReport($node);

        if ($report) {
            // Update existing report
            $this->_reportingService->updateReport($node);
        } else {
            // Create new report
            $this->_reportingService->createReport($node);
        }

    }//end _handleNodeWritten()


    /**
     * Handle node deleted event
     *
     * @param NodeDeletedEvent $event The node deleted event
     *
     * @return void
     */
    private function _handleNodeDeleted(NodeDeletedEvent $event): void
    {
        $node = $event->getNode();


        $this->_logger->debug(
                'File deleted: '.$node->getName(),
                [
                    'node_id' => $node->getId(),
                    'path'    => $node->getPath(),
                ]
                );

        // No report creation needed for deleted files    }//end _handleNodeDeleted()


        /**
         * Handle node touched event
         *
         * @param NodeTouchedEvent $event The node touched event
         *
         * @return void
         */
    private function _handleNodeTouched(NodeTouchedEvent $event): void
        {
            $node = $event->getNode();


            $this->_logger->debug(
                'File touched: '.$node->getName(),
                [
                    'node_id' => $node->getId(),
                    'path'    => $node->getPath(),
                ]
                );

            // No report creation needed for touched files (metadata only changes)    }//end _handleNodeTouched()    }//end _handleNodeTouched()
