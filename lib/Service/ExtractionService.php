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

use Exception;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use Psr\Log\LoggerInterface;

/**
 * Service for extracting text content from various file types
 *
 * This service provides methods to extract text content from different file formats
 * including PDF, Word documents, Excel spreadsheets, PowerPoint presentations,
 * and plain text files.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later
 * @link     https://github.com/conductionnl/docudesk
 */
class ExtractionService
{
    /**
     * Logger instance for error reporting
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * Constructor for ExtractionService
     *
     * @param LoggerInterface $logger Logger for error reporting
     *
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Extract text content from a file node
     *
     * This method detects the file type based on extension and calls the appropriate
     * extraction method.
     *
     * @param \OCP\Files\Node $node The file node to extract text from
     *
     * @return string|null Extracted text content or null if extraction failed
     *
     * @throws \InvalidArgumentException If the node is not a file
     * @throws Exception If the file type is not supported or extraction fails
     *
     * @psalm-return string|null
     * @phpstan-return string|null
     */
    public function extractText(\OCP\Files\Node $node): ?string
    {
        // Check if node is a file
        if ($node->getType() !== \OCP\Files\FileInfo::TYPE_FILE) {
            throw new \InvalidArgumentException('Node must be a file');
        }

        // Get file path and MIME type
        $filePath = $node->getPath();
        $mimeType = $node->getMimeType();
        $extension = $node->getExtension();

        // Initialize extraction result
        $extraction = [
            'text' => null,
            'errorMessage' => null
        ];

        // Extract text based on file type
        switch ($extension) {
            case 'pdf':
                $extraction['text'] = $this->extractFromPdf($filePath);
                return $extraction;
            case 'doc':
            case 'docx':
                $extraction['text'] = $this->extractFromWord($filePath);
                return $extraction;
            case 'xls':
            case 'xlsx':
            case 'csv':
                $extraction['text'] = $this->extractFromSpreadsheet($filePath);
                return $extraction;
            case 'ppt':
            case 'pptx':
                $extraction['text'] = $this->extractFromPresentation($filePath);
                return $extraction;
            case 'txt':
            case 'md':
            case 'html':
            case 'htm':
            case 'xml':
            case 'json':
                $extraction['text'] = $this->extractFromTextFile($filePath);
                return $extraction;
            // Image files - return empty string with a log message
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp':
            case 'svg':
            case 'tiff':
                $this->logger->info('File is an image, no text extraction possible: ' . $filePath);
                $extraction['errorMessage'] = 'File is an image, no text extraction possible';
                return $extraction;
            // Video files - return empty string with a log message
            case 'mp4':
            case 'avi':
            case 'mov':
            case 'wmv':
            case 'flv':
            case 'webm':
            case 'mkv':
                $this->logger->info('File is a video, no text extraction possible: ' . $filePath);
                $extraction['errorMessage'] = 'File is a video, no text extraction possible';
                return $extraction;
            // Audio files - return empty string with a log message
            case 'mp3':
            case 'wav':
            case 'ogg':
            case 'flac':
            case 'aac':
            case 'm4a':
                $this->logger->info('File is an audio file, no text extraction possible: ' . $filePath);
                $extraction['errorMessage'] = 'File is an audio file, no text extraction possible';
                return $extraction;
            default:
                // Log warning and throw exception for unsupported file type
                $this->logger->warning('Unsupported file type: ' . $extension . ' with MIME type: ' . $mimeType);
                $extraction['errorMessage'] = 'Unsupported file type: ' . $extension . ' with MIME type: ' . $mimeTyp;
                return $extraction;
        }
    }

    /**
     * Extract text from a PDF file
     *
     * @param string $filePath Path to the PDF file
     *
     * @return string Extracted text content
     *
     * @throws Exception If PDF parsing fails
     *
     * @psalm-return string
     * @phpstan-return string
     */
    private function extractFromPdf(string $filePath): string
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
    }

    /**
     * Extract text from a Word document
     *
     * @param string $filePath Path to the Word document
     *
     * @return string Extracted text content
     *
     * @throws Exception If Word document parsing fails
     *
     * @psalm-return string
     * @phpstan-return string
     */
    private function extractFromWord(string $filePath): string
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
                    $text .= $element->getText() . ' ';
                } elseif (method_exists($element, 'getElements')) {
                    // Handle tables and other container elements
                    $childElements = $element->getElements();
                    foreach ($childElements as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText() . ' ';
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
    }

    /**
     * Extract text from a spreadsheet (Excel, CSV)
     *
     * @param string $filePath Path to the spreadsheet file
     *
     * @return string Extracted text content
     *
     * @throws Exception If spreadsheet parsing fails
     *
     * @psalm-return string
     * @phpstan-return string
     */
    private function extractFromSpreadsheet(string $filePath): string
    {
        // Load the spreadsheet
        $spreadsheet = SpreadsheetIOFactory::load($filePath);
        
        $text = '';
        
        // Iterate through all worksheets
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            // Add worksheet title
            $text .= 'Sheet: ' . $worksheet->getTitle() . "\n";
            
            // Iterate through all rows and columns
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $rowText = '';
                foreach ($cellIterator as $cell) {
                    $value = $cell->getValue();
                    if (!empty($value)) {
                        $rowText .= $value . "\t";
                    }
                }
                
                if (!empty(trim($rowText))) {
                    $text .= trim($rowText) . "\n";
                }
            }
            
            $text .= "\n";
        }
        
        return trim($text);
    }

    /**
     * Extract text from a presentation (PowerPoint)
     *
     * @param string $filePath Path to the presentation file
     *
     * @return string Extracted text content
     *
     * @throws Exception If presentation parsing fails
     *
     * @psalm-return string
     * @phpstan-return string
     */
    private function extractFromPresentation(string $filePath): string
    {
        // Load the presentation
        $presentation = PresentationIOFactory::load($filePath);
        
        $text = '';
        
        // Iterate through all slides
        $slideCount = $presentation->getSlideCount();
        for ($i = 0; $i < $slideCount; $i++) {
            $slide = $presentation->getSlide($i);
            
            // Add slide number
            $text .= 'Slide ' . ($i + 1) . ":\n";
            
            // Extract text from shapes
            foreach ($slide->getShapeCollection() as $shape) {
                if (method_exists($shape, 'getText')) {
                    $shapeText = $shape->getText();
                    if (!empty($shapeText)) {
                        $text .= $shapeText . "\n";
                    }
                }
                
                // Extract text from rich text elements
                if (method_exists($shape, 'getRichTextElements')) {
                    foreach ($shape->getRichTextElements() as $richText) {
                        if (method_exists($richText, 'getText')) {
                            $richTextContent = $richText->getText();
                            if (!empty($richTextContent)) {
                                $text .= $richTextContent . "\n";
                            }
                        }
                    }
                }
            }
            
            $text .= "\n";
        }
        
        return trim($text);
    }

    /**
     * Extract text from a plain text file
     *
     * @param string $filePath Path to the text file
     *
     * @return string Extracted text content
     *
     * @throws Exception If file reading fails
     *
     * @psalm-return string
     * @phpstan-return string
     */
    private function extractFromTextFile(string $filePath): string
    {
        // Read file contents
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            throw new Exception('Failed to read file: ' . $filePath);
        }
        
        return $content;
    }

    /**
     * Get metadata from a file
     *
     * @param string $filePath Path to the file
     *
     * @return array<string, mixed> Metadata information
     *
     * @throws Exception If metadata extraction fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    public function extractMetadata(string $filePath): array
    {
        // Basic file metadata
        $metadata = [
            'filename' => basename($filePath),
            'filesize' => filesize($filePath),
            'filetype' => mime_content_type($filePath),
            'extension' => strtolower(pathinfo($filePath, PATHINFO_EXTENSION)),
            'last_modified' => filemtime($filePath),
        ];

        // Get file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            // Extract additional metadata based on file type
            switch ($extension) {
                case 'pdf':
                    $metadata = array_merge($metadata, $this->extractPdfMetadata($filePath));
                    break;
                case 'doc':
                case 'docx':
                    $metadata = array_merge($metadata, $this->extractWordMetadata($filePath));
                    break;
                case 'xls':
                case 'xlsx':
                    $metadata = array_merge($metadata, $this->extractSpreadsheetMetadata($filePath));
                    break;
                case 'ppt':
                case 'pptx':
                    $metadata = array_merge($metadata, $this->extractPresentationMetadata($filePath));
                    break;
            }
        } catch (Exception $e) {
            $this->logger->warning('Error extracting metadata: ' . $e->getMessage(), ['exception' => $e]);
            // Continue with basic metadata if advanced extraction fails
        }

        return $metadata;
    }

    /**
     * Extract metadata from a PDF file
     *
     * @param string $filePath Path to the PDF file
     *
     * @return array<string, mixed> PDF metadata
     *
     * @throws Exception If PDF parsing fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function extractPdfMetadata(string $filePath): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $details = $pdf->getDetails();
        
        // Extract relevant metadata
        $metadata = [];
        
        // Common PDF metadata fields
        $metadataFields = [
            'Title', 'Author', 'Subject', 'Keywords', 'Creator', 
            'Producer', 'CreationDate', 'ModDate', 'Pages'
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
    }

    /**
     * Extract metadata from a Word document
     *
     * @param string $filePath Path to the Word document
     *
     * @return array<string, mixed> Word document metadata
     *
     * @throws Exception If Word document parsing fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function extractWordMetadata(string $filePath): array
    {
        $phpWord = WordIOFactory::load($filePath);
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
        $wordCount = 0;
        
        foreach ($sections as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if (get_class($element) === 'PhpOffice\PhpWord\Element\TextRun') {
                    $paragraphCount++;
                    // Count words in text
                    if (method_exists($element, 'getText')) {
                        $text = $element->getText();
                        $wordCount += str_word_count($text);
                    }
                }
            }
        }
        
        $metadata['paragraphs'] = $paragraphCount;
        $metadata['words'] = $wordCount;
        
        return $metadata;
    }

    /**
     * Extract metadata from a spreadsheet
     *
     * @param string $filePath Path to the spreadsheet file
     *
     * @return array<string, mixed> Spreadsheet metadata
     *
     * @throws Exception If spreadsheet parsing fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function extractSpreadsheetMetadata(string $filePath): array
    {
        $spreadsheet = SpreadsheetIOFactory::load($filePath);
        $properties = $spreadsheet->getProperties();
        
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
        
        // Count worksheets and cells
        $worksheets = $spreadsheet->getAllSheets();
        $metadata['worksheets'] = count($worksheets);
        
        $cellCount = 0;
        $nonEmptyCellCount = 0;
        
        foreach ($worksheets as $worksheet) {
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            $cellCount += $highestRow * $highestColumnIndex;
            
            // Count non-empty cells
            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                    if (!empty($cellValue)) {
                        $nonEmptyCellCount++;
                    }
                }
            }
        }
        
        $metadata['total_cells'] = $cellCount;
        $metadata['non_empty_cells'] = $nonEmptyCellCount;
        
        return $metadata;
    }

    /**
     * Extract metadata from a presentation
     *
     * @param string $filePath Path to the presentation file
     *
     * @return array<string, mixed> Presentation metadata
     *
     * @throws Exception If presentation parsing fails
     *
     * @psalm-return array<string, mixed>
     * @phpstan-return array<string, mixed>
     */
    private function extractPresentationMetadata(string $filePath): array
    {
        $presentation = PresentationIOFactory::load($filePath);
        $properties = $presentation->getDocumentProperties();
        
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
        
        // Count slides and shapes
        $slideCount = $presentation->getSlideCount();
        $metadata['slides'] = $slideCount;
        
        $shapeCount = 0;
        for ($i = 0; $i < $slideCount; $i++) {
            $slide = $presentation->getSlide($i);
            $shapeCount += count($slide->getShapeCollection());
        }
        
        $metadata['shapes'] = $shapeCount;
        
        return $metadata;
    }
}

