<?php

/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license   GNU AGPL version 3 or any later version
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

use Exception;
use Psr\Log\LoggerInterface;
use OCA\OpenRegister\Service\ObjectService;
use OCP\IAppConfig;
use OCA\DocuDesk\Service\ExtractionHandlers\ExtractionHandlerInterface;
use OCA\DocuDesk\Service\ExtractionHandlers\PdfExtractionHandler;
use OCA\DocuDesk\Service\ExtractionHandlers\WordExtractionHandler;

/**
 * Service for extracting text content from various file types
 *
 * This service provides methods to extract text content from different file formats
 * including PDF, Word documents, Excel spreadsheets, PowerPoint presentations,
 * and plain text files.
 */
class ExtractionService
{

    /**
     * Logger instance for error reporting
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $_logger;

    /**
     * Array of registered extraction handlers
     *
     * @var ExtractionHandlerInterface[]
     */
    private array $_handlers;


    /**
     * Constructor for ExtractionService
     *
     * @param LoggerInterface $logger Logger for error reporting
     */

    /**
     * App config for getting app config
     *
     * @var IAppConfig
     */
    private readonly IAppConfig $appConfig;

    public function __construct(LoggerInterface $logger, IAppConfig $appConfig)
    {
        $this->_logger   = $logger;
        $this->_handlers = [
            new PdfExtractionHandler($logger),
            new WordExtractionHandler($logger),
        ];

    }//end __construct()


    /**
     * Extract text content from a file node
     *
     * This method detects the file type based on extension and calls the appropriate
     * extraction method.
     *
     * @param \OCP\Files\Node $node The file node to extract text from
     *
     * @return array{text: ?string, errorMessage: ?string} Extraction result with text content and error message
     *
     * @throws \InvalidArgumentException If the node is not a file
     * @throws Exception If the file type is not supported or extraction fails
     */
    public function extractText(\OCP\Files\Node $node): array
    {
        // Check if node is a file.
        if ($node->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
            throw new \InvalidArgumentException('Node must be a file');
        }

        // Get file path and MIME type.
        $filePath  = $node->getPath();
        $mimeType  = $node->getMimeType();
        $extension = $node->getExtension();

        // Initialize extraction result.
        $extraction = [
            'text'         => null,
            'errorMessage' => null,
        ];

        try {
            // Find appropriate handler.
            $handler = $this->_findHandler($extension, $mimeType);

            if ($handler !== null) {
                $this->_logger->debug('Found handler for file: '.$filePath);
                $extraction['text'] = $handler->extractText($filePath);
            } else {
                // Handle text files directly.
                if (in_array($extension, ['txt', 'md', 'html', 'htm', 'xml', 'json'])) {
                    $this->_logger->debug('File is a text file, extracting text: '.$filePath);
                    $extraction['text'] = $node->getContent();
                } else {
                    // Log warning for unsupported file type.
                    $this->_logger->warning('Unsupported file type: '.$extension.' with MIME type: '.$mimeType);
                    $extraction['errorMessage'] = 'Unsupported file type: '.$extension.' with MIME type: '.$mimeType;
                }
            }
        } catch (Exception $e) {
            $this->_logger->error('Error extracting text: '.$e->getMessage(), ['exception' => $e]);
            $extraction['errorMessage'] = 'Error extracting text: '.$e->getMessage();
        }//end try.

        return $extraction;

    }//end extractText()


    /**
     * Extract metadata from a file
     *
     * @param string $filePath Path to the file
     *
     * @return array<string, mixed> Metadata information
     *
     * @throws Exception If metadata extraction fails
     */
    public function extractMetadata(string $filePath): array
    {
        // Basic file metadata.
        $metadata = [
            'filename'      => basename($filePath),
            'filesize'      => filesize($filePath),
            'filetype'      => mime_content_type($filePath),
            'extension'     => strtolower(pathinfo($filePath, PATHINFO_EXTENSION)),
            'last_modified' => filemtime($filePath),
        ];

        try {
            // Get file extension and MIME type.
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType  = mime_content_type($filePath);

            // Find appropriate handler.
            $handler = $this->_findHandler($extension, $mimeType);

            if ($handler !== null) {
                // Merge handler metadata with basic metadata.
                $metadata = array_merge($metadata, $handler->extractMetadata($filePath));
            }
        } catch (Exception $e) {
            $this->_logger->warning('Error extracting metadata: '.$e->getMessage(), ['exception' => $e]);
            // Continue with basic metadata if advanced extraction fails.
        }

        return $metadata;

    }//end extractMetadata()


    /**
     * Find an appropriate handler for the given file type
     *
     * @param string $extension File extension
     * @param string $mimeType  File MIME type
     *
     * @return ExtractionHandlerInterface|null Handler that supports the file type, or null if none found
     */
    private function _findHandler(string $extension, string $mimeType): ?ExtractionHandlerInterface
    {
        foreach ($this->_handlers as $handler) {
            if ($handler->supports($extension, $mimeType)) {
                return $handler;
            }
        }

        return null;

    }//end _findHandler()


}//end class
