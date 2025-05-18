# Extraction Handlers

The DocuDesk application uses a handler-based architecture for extracting text and metadata from different file types. This document describes the extraction handlers and how they work.

## Overview

Extraction handlers are responsible for extracting text content and metadata from specific file types. Each handler implements the `ExtractionHandlerInterface` and provides functionality for a particular file format.

## Available Handlers

### PDF Handler

The PDF handler (`PdfExtractionHandler`) extracts text and metadata from PDF files. It uses the `smalot/pdfparser` library to parse PDF documents.

Supported file types:
- `.pdf` files
- MIME type: `application/pdf`

### Word Handler

The Word handler (`WordExtractionHandler`) extracts text and metadata from Microsoft Word documents. It uses the `phpoffice/phpword` library to parse Word documents.

Supported file types:
- `.doc` and `.docx` files
- MIME types:
  - `application/msword`
  - `application/vnd.openxmlformats-officedocument.wordprocessingml.document`

## Usage

The extraction handlers are used by the `ExtractionService` class, which manages the handlers and provides a unified interface for text and metadata extraction.

Example usage:

```php
// Get the extraction service
$extractionService = $container->get(ExtractionService::class);

// Extract text from a file
$result = $extractionService->extractText($fileNode);
$text = $result['text'];
$errorMessage = $result['errorMessage'];

// Extract metadata from a file
$metadata = $extractionService->extractMetadata($filePath);
```

## Creating New Handlers

To create a new extraction handler:

1. Create a new class that implements `ExtractionHandlerInterface`
2. Implement the required methods:
   - `extractText(string $filePath): string`
   - `extractMetadata(string $filePath): array`
   - `supports(string $extension, string $mimeType): bool`
3. Register the handler in the `ExtractionService` constructor

Example handler:

```php
class MyHandler implements ExtractionHandlerInterface
{
    public function extractText(string $filePath): string
    {
        // Extract text from file
        return $text;
    }

    public function extractMetadata(string $filePath): array
    {
        // Extract metadata from file
        return $metadata;
    }

    public function supports(string $extension, string $mimeType): bool
    {
        // Check if this handler supports the file type
        return $extension === 'myext' || $mimeType === 'application/mytype';
    }
}
```

## Testing

Each handler has its own test class that verifies its functionality. The tests cover:

- File type support detection
- Text extraction
- Metadata extraction
- Error handling

To run the tests:

```bash
vendor/bin/phpunit tests/Unit/Service/ExtractionHandlers
``` 