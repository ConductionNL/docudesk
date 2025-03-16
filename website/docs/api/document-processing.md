---
sidebar_position: 4
title: Document Processing
---

# Document Processing

DocuDesk provides powerful document processing capabilities that allow you to transform, analyze, and manage documents in various formats. This page explains how document processing works in DocuDesk.

## Overview

The Document Processing system in DocuDesk enables you to:

- Generate documents from templates
- Convert documents between formats
- Extract text and metadata from documents
- Anonymize personal data in documents
- Check documents for accessibility compliance
- Validate documents against templates or schemas

## Processing Workflow

A typical document processing workflow in DocuDesk consists of the following steps:

1. **Initiation**: A user or system initiates a processing operation
2. **Queuing**: The operation is queued for processing
3. **Processing**: The document is processed according to the requested operation
4. **Logging**: The operation is logged in the parsing logs
5. **Privacy Tracking**: If applicable, privacy-related metadata is updated
6. **Report Generation**: A document report is generated with analysis results
7. **Notification**: The user is notified of the operation's completion

## Integration with Other Systems

Document processing in DocuDesk is tightly integrated with several other systems:

- **[Privacy Tracking](./privacy-tracking.md)**: Tracks privacy-related metadata for documents
- **[Parsing Logs](./parsing-logs.md)**: Logs document processing operations
- **[Document Reports](./document-reports.md)**: Stores analysis results for documents
- **[Anonymization Logs](./anonymization-logs.md)**: Tracks anonymization operations and replacements

This integration ensures that you have a complete audit trail of all document operations and can demonstrate GDPR compliance.

## Processing Operations

### Text Extraction

Text extraction allows you to extract the textual content from documents in various formats (PDF, Word, etc.). This is useful for:

- Indexing documents for search
- Analyzing document content
- Preparing documents for anonymization

### Metadata Extraction

Metadata extraction allows you to extract metadata from documents, such as:

- Author information
- Creation and modification dates
- Document properties
- Embedded metadata

### Anonymization

Anonymization allows you to remove or mask personal data in documents. DocuDesk supports:

- Named entity recognition to identify personal data
- Redaction of personal data
- Pseudonymization of personal data
- Verification of anonymization results
- De-anonymization with secure keys

The results of anonymization operations are stored in the [Anonymization Log](./anonymization-logs.md) object, which includes:

- Original and anonymized text
- Detailed information about detected entities
- A secure key for potential de-anonymization
- A list of all text replacements made

For more information on anonymization, see the [Anonymization Logs](./anonymization-logs.md) documentation.

### Format Conversion

Format conversion allows you to convert documents between various formats:

- PDF to Word
- Word to PDF
- HTML to PDF
- Excel to CSV
- And many more

### Accessibility Check

Accessibility checking allows you to verify that documents comply with accessibility standards (WCAG):

- Checking for proper document structure
- Verifying alt text for images
- Ensuring proper color contrast
- Checking for other accessibility issues

The results of accessibility checks are stored in the [Document Report](./document-reports.md) object.

### Language Level Analysis

Language level analysis allows you to assess the readability and complexity of document text:

- Readability scores (Flesch-Kincaid, SMOG Index, etc.)
- Text complexity metrics
- Education level assessment
- Language improvement suggestions

The results of language level analysis are stored in the [Document Report](./document-reports.md) object.

### Validation

Validation allows you to verify that documents conform to templates or schemas:

- Checking for required fields
- Validating field formats
- Ensuring document structure matches templates
- Verifying document integrity

## API Integration

Document processing operations can be initiated through the DocuDesk API. The typical flow is:

1. Create a parsing log entry with the desired operation type
2. Monitor the parsing log for status updates
3. Retrieve the document report or anonymization log when the operation is complete

For detailed API documentation, see the [OpenAPI Specification](./openapi.md).

## Best Practices

1. **Batch Processing**: For large numbers of documents, use batch processing to improve efficiency
2. **Error Handling**: Implement proper error handling to deal with failed processing operations
3. **Monitoring**: Regularly monitor parsing logs to identify issues and bottlenecks
4. **Privacy Compliance**: Always update privacy tracking records when processing documents with personal data
5. **Performance Optimization**: Configure processing operations for optimal performance based on your needs
6. **Report Analysis**: Regularly review document reports to identify compliance issues
7. **Secure Key Management**: Store anonymization keys securely for potential de-anonymization

## Examples

### Anonymizing a Document

```php
// Create a parsing log entry for anonymization
$parsingLog = new ParsingLog();
$parsingLog->setFileId('file123');
$parsingLog->setFileName('sensitive-document.pdf');
$parsingLog->setParsingType('anonymization');
$parsingLog->setStatus('pending');
$parsingLog->save();

// Process the document (this would typically be handled by a background job)
$processor = new DocumentProcessor();
$result = $processor->anonymize('file123');

// Update the parsing log
$parsingLog->setStatus('completed');
$parsingLog->setCompletedAt(new DateTime());
$parsingLog->setOutputFileId($result->getOutputFileId());
$parsingLog->save();

// Update the privacy tracking record
$privacyFile = PrivacyFile::findByFileId('file123');
$privacyFile->setAnonymizationStatus('completed');
$privacyFile->setAnonymizationDate(new DateTime());
$privacyFile->save();

// Create an anonymization log
$anonymizationLog = new AnonymizationLog();
$anonymizationLog->setNodeId('file123');
$anonymizationLog->setFileName('sensitive-document.pdf');
$anonymizationLog->setStatus('completed');
$anonymizationLog->setOriginalText($result->getOriginalText());
$anonymizationLog->setAnonymizedText($result->getAnonymizedText());
$anonymizationLog->setEntitiesFound($result->getEntitiesFound());
$anonymizationLog->setEntityReplacements($result->getEntityReplacements());
$anonymizationLog->setAnonymizationKey($result->generateSecureKey());
$anonymizationLog->setOutputNodeId($result->getOutputNodeId());
$anonymizationLog->save();

// Create a document report
$reportData = [
    'node_id' => 'file123',
    'file_name' => 'sensitive-document.pdf',
    'file_hash' => hash_file('sha256', $result->getOutputFilePath()),
    'analysis_types' => ['anonymization']
];

$reportService = new DocumentReportService();
$report = $reportService->createReport($reportData);
$report->setAnonymizationResults($result->getAnonymizationResults());
$report->setStatus('completed');
$report->save();
```

### Converting a Document

```php
// Create a parsing log entry for format conversion
$parsingLog = new ParsingLog();
$parsingLog->setFileId('file456');
$parsingLog->setFileName('document.docx');
$parsingLog->setParsingType('format_conversion');
$parsingLog->setOutputFormat('pdf');
$parsingLog->setStatus('pending');
$parsingLog->save();

// Process the document
$processor = new DocumentProcessor();
$result = $processor->convert('file456', 'pdf');

// Update the parsing log
$parsingLog->setStatus('completed');
$parsingLog->setCompletedAt(new DateTime());
$parsingLog->setOutputFileId($result->getOutputFileId());
$parsingLog->save();
```

### Analyzing Document Accessibility

```php
// Create a document report for WCAG compliance analysis
$reportData = [
    'node_id' => 'file789',
    'file_name' => 'document.pdf',
    'file_hash' => hash_file('sha256', '/path/to/document.pdf'),
    'analysis_types' => ['wcag_compliance']
];

$reportService = new DocumentReportService();
$report = $reportService->createReport($reportData);

// Process the document
$processor = new DocumentProcessor();
$result = $processor->analyzeAccessibility('file789');

// Update the report
$report->setWcagComplianceResults($result->getWcagResults());
$report->setStatus('completed');
$report->save();
```

## Conclusion

Document processing is a core feature of DocuDesk that enables powerful document transformation and analysis capabilities. By integrating with privacy tracking, parsing logs, document reports, and anonymization logs, DocuDesk ensures that all document operations are properly tracked and comply with privacy and accessibility regulations. 