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

use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Psr\Log\LoggerInterface;

/**
 * Handler for extracting text and metadata from Word documents
 *
 * This handler provides functionality to extract text content and metadata
 * from Microsoft Word documents (.doc and .docx files).
 *
 * @category Service
 * @package  OCA\DocuDesk\Service\ExtractionHandlers
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @link     https://github.com/conductionnl/docudesk
 */
class WordExtractionHandler implements ExtractionHandlerInterface
{

    /**
     * Logger instance for error reporting
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $_logger;


    /**
     * Constructor for WordExtractionHandler
     *
     * @param LoggerInterface $logger Logger for error reporting
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;

    }//end __construct()


    /**
     * Extract text content from a Word document
     *
     * @param string $filePath Path to the Word document
     *
     * @return string Extracted text content
     *
     * @throws \Exception If Word document parsing fails
     */
    public function extractText(string $filePath): string
    {
        // Load the document
        $phpWord = WordIOFactory::load($filePath);

        $text = '';

        // Iterate through all sections
        $sections = $phpWord->getSections();
        foreach ($sections as $section) {
            // Get all elements in the section
            $elements = $section->getElements();
            foreach ($elements as $element) {
                // Extract text from text elements
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText().' ';
                } else if (method_exists($element, 'getElements')) {
                    // Handle tables and other container elements
                    $childElements = $element->getElements();
                    foreach ($childElements as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText().' ';
                        }
                    }
                }
            }

            $text .= "\n";
        }

        // Clean up text
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;

    }//end extractText()


    /**
     * Extract metadata from a Word document
     *
     * @param string $filePath Path to the Word document
     *
     * @return array<string, mixed> Extracted metadata
     *
     * @throws \Exception If Word document parsing fails
     */
    public function extractMetadata(string $filePath): array
    {
        $phpWord    = WordIOFactory::load($filePath);
        $properties = $phpWord->getDocInfo();

        $metadata = [];

        // Extract document properties
        if ($properties->getCreator()) {
            $metadata['creator'] = $properties->getCreator();
        }

        if ($properties->getLastModifiedBy()) {
            $metadata['last_modified_by'] = $properties->getLastModifiedBy();
        }

        if ($properties->getCreated()) {
            $metadata['created'] = $properties->getCreated();
        }

        if ($properties->getModified()) {
            $metadata['modified'] = $properties->getModified();
        }

        if ($properties->getTitle()) {
            $metadata['title'] = $properties->getTitle();
        }

        if ($properties->getDescription()) {
            $metadata['description'] = $properties->getDescription();
        }

        if ($properties->getSubject()) {
            $metadata['subject'] = $properties->getSubject();
        }

        if ($properties->getKeywords()) {
            $metadata['keywords'] = $properties->getKeywords();
        }

        if ($properties->getCategory()) {
            $metadata['category'] = $properties->getCategory();
        }

        // Count sections, paragraphs, and words
        $sections = $phpWord->getSections();
        $metadata['sections'] = count($sections);

        $paragraphCount = 0;
        $wordCount      = 0;

        foreach ($sections as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if (get_class($element) === 'PhpOffice\PhpWord\Element\TextRun') {
                    $paragraphCount++;
                    // Count words in text
                    if (method_exists($element, 'getText')) {
                        $text       = $element->getText();
                        $wordCount += str_word_count($text);
                    }
                }
            }
        }

        $metadata['paragraphs'] = $paragraphCount;
        $metadata['words']      = $wordCount;

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
        return in_array($extension, ['doc', 'docx']) ||
            in_array(
                    $mimeType,
                    [
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ]
                    );

    }//end supports()


}//end class
