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

use Smalot\PdfParser\Parser as PdfParser;
use Psr\Log\LoggerInterface;

/**
 * Handler for extracting text and metadata from PDF documents
 *
 * This handler provides functionality to extract text content and metadata
 * from PDF documents.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service\ExtractionHandlers
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @link     https://github.com/conductionnl/docudesk
 */
class PdfExtractionHandler implements ExtractionHandlerInterface
{

    /**
     * Logger instance for error reporting
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $_logger;


    /**
     * Constructor for PdfExtractionHandler
     *
     * @param LoggerInterface $logger Logger for error reporting
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;

    }//end __construct()


    /**
     * Extract text content from a PDF file
     *
     * @param string $filePath Path to the PDF file
     *
     * @return string Extracted text content
     *
     * @throws \Exception If PDF parsing fails
     */
    public function extractText(string $filePath): string
    {
        // Create PDF parser instance
        $parser = new PdfParser();

        // Parse PDF file
        $pdf = $parser->parseFile($filePath);

        // Extract text from all pages
        $text = $pdf->getText();

        // Clean up text (remove excessive whitespace)
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;

    }//end extractText()


    /**
     * Extract metadata from a PDF file
     *
     * @param string $filePath Path to the PDF file
     *
     * @return array<string, mixed> Extracted metadata
     *
     * @throws \Exception If PDF parsing fails
     */
    public function extractMetadata(string $filePath): array
    {
        $parser  = new PdfParser();
        $pdf     = $parser->parseFile($filePath);
        $details = $pdf->getDetails();

        // Extract relevant metadata
        $metadata = [];

        // Common PDF metadata fields
        $metadataFields = [
            'Title',
            'Author',
            'Subject',
            'Keywords',
            'Creator',
            'Producer',
            'CreationDate',
            'ModDate',
            'Pages',
        ];

        foreach ($metadataFields as $field) {
            if (isset($details[$field])) {
                $metadata[strtolower($field)] = $details[$field];
            }
        }

        // Add page count
        if (!isset($metadata['pages']) && $pdf->getPages() !== null) {
            $metadata['pages'] = count($pdf->getPages());
        }

        return $metadata;

    }//end extractMetadata()


    /**
     * Check if this handler supports the given file type
     *
     * @param string $extension File extension
     * @param string $mimeType  File MIME type
     *
     * @return bool True if this handler supports the file type
     */
    public function supports(string $extension, string $mimeType): bool
    {
        return $extension === 'pdf' || $mimeType === 'application/pdf';

    }//end supports()


}//end class
