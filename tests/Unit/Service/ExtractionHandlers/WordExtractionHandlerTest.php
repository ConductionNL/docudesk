<?php

/**
 * @category Test
 * @package  OCA\DocuDesk\Tests\Unit\Service\ExtractionHandlers
 * @author   Conduction B.V. <info@conduction.nl>
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @link     https://github.com/conductionnl/docudesk
 */

namespace OCA\DocuDesk\Tests\Unit\Service\ExtractionHandlers;

use OCA\DocuDesk\Service\ExtractionHandlers\WordExtractionHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for WordExtractionHandler
 *
 * @category Test
 * @package  OCA\DocuDesk\Tests\Unit\Service\ExtractionHandlers
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @link     https://github.com/conductionnl/docudesk
 */
class WordExtractionHandlerTest extends TestCase
{
    /**
     * Logger instance for testing
     *
     * @var LoggerInterface
     */
    private LoggerInterface $_logger;

    /**
     * Handler instance for testing
     *
     * @var WordExtractionHandler
     */
    private WordExtractionHandler $_handler;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock logger
        $this->_logger = $this->createMock(LoggerInterface::class);
        
        // Create handler instance
        $this->_handler = new WordExtractionHandler($this->_logger);
    }

    /**
     * Test supports method with valid file types
     *
     * @return void
     */
    public function testSupportsValidFileTypes(): void
    {
        // Test .doc extension
        $this->assertTrue($this->_handler->supports('doc', 'application/msword'));
        
        // Test .docx extension
        $this->assertTrue($this->_handler->supports('docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'));
        
        // Test MIME type without extension
        $this->assertTrue($this->_handler->supports('', 'application/msword'));
        $this->assertTrue($this->_handler->supports('', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'));
    }

    /**
     * Test supports method with invalid file types
     *
     * @return void
     */
    public function testSupportsInvalidFileTypes(): void
    {
        // Test invalid extension
        $this->assertFalse($this->_handler->supports('pdf', 'application/pdf'));
        $this->assertFalse($this->_handler->supports('txt', 'text/plain'));
        
        // Test invalid MIME type
        $this->assertFalse($this->_handler->supports('doc', 'application/pdf'));
        $this->assertFalse($this->_handler->supports('docx', 'text/plain'));
    }

    /**
     * Test extractText method with valid file
     *
     * @return void
     */
    public function testExtractTextWithValidFile(): void
    {
        // Create a temporary test file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'Test content');
        
        try {
            // Test text extraction
            $text = $this->_handler->extractText($tempFile);
            
            // Verify the extracted text
            $this->assertIsString($text);
            $this->assertNotEmpty($text);
        } finally {
            // Clean up
            unlink($tempFile);
        }
    }

    /**
     * Test extractText method with invalid file
     *
     * @return void
     */
    public function testExtractTextWithInvalidFile(): void
    {
        $this->expectException(\Exception::class);
        $this->_handler->extractText('nonexistent_file.doc');
    }

    /**
     * Test extractMetadata method with valid file
     *
     * @return void
     */
    public function testExtractMetadataWithValidFile(): void
    {
        // Create a temporary test file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'Test content');
        
        try {
            // Test metadata extraction
            $metadata = $this->_handler->extractMetadata($tempFile);
            
            // Verify the extracted metadata
            $this->assertIsArray($metadata);
            $this->assertNotEmpty($metadata);
        } finally {
            // Clean up
            unlink($tempFile);
        }
    }

    /**
     * Test extractMetadata method with invalid file
     *
     * @return void
     */
    public function testExtractMetadataWithInvalidFile(): void
    {
        $this->expectException(\Exception::class);
        $this->_handler->extractMetadata('nonexistent_file.doc');
    }
} 