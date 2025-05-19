<?php

/**
 * @category  Service
 * @package   OCA\DocuDesk\Service\ExtractionHandlers
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @link      https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\Service\ExtractionHandlers;

/**
 * Interface for file extraction handlers
 *
 * This interface defines the contract that all extraction handlers must implement
 * to provide text extraction functionality for specific file types.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service\ExtractionHandlers
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @link     https://github.com/conductionnl/docudesk
 */
interface ExtractionHandlerInterface
{


    /**
     * Extract text content from a file
     *
     * @param string $filePath Path to the file to extract text from
     *
     * @return string Extracted text content
     *
     * @throws \Exception If extraction fails
     */
    public function extractText(string $filePath): string;


    /**
     * Extract metadata from a file
     *
     * @param string $filePath Path to the file to extract metadata from
     *
     * @return array<string, mixed> Extracted metadata
     *
     * @throws \Exception If metadata extraction fails
     */
    public function extractMetadata(string $filePath): array;


    /**
     * Check if this handler supports the given file type
     *
     * @param string $extension File extension
     * @param string $mimeType  File MIME type
     *
     * @return bool True if this handler supports the file type
     */
    public function supports(string $extension, string $mimeType): bool;


}//end interface
