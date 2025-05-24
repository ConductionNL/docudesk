# Entity Management

DocuDesk provides advanced entity management capabilities to handle detected sensitive information across multiple documents. This system ensures consistent handling of entities and provides detailed tracking and anonymization controls.

## Overview

The entity management system consists of:
- **Entity Objects**: Centralized storage for detected entities
- **Entity Processing**: Automatic deduplication and enhancement
- **Entity Statistics**: Tracking occurrence counts and confidence scores
- **Anonymization Control**: Per-entity anonymization settings stored on reports
- **Robust Error Handling**: Comprehensive error handling and fallback mechanisms
- **Performance Optimization**: Automatic skipping of anonymized files to prevent unnecessary processing

## Performance Optimizations

### Anonymized File Handling

To optimize performance and prevent unnecessary processing overhead, DocuDesk automatically skips report generation for files that end with '_anonymized'. This prevents:

- **Duplicate Processing**: Anonymized files are already processed results and don't need entity detection
- **Performance Drain**: Avoiding unnecessary text extraction and Presidio API calls
- **Resource Usage**: Reducing database operations and storage requirements
- **Report Clutter**: Preventing duplicate reports in the system

The system implements this optimization at multiple levels:

1. **FileEventListener**: Primary check when files are created/uploaded
2. **ReportingService**: Safeguard check in createReport method
3. **Logging**: Debug information when files are skipped

```php
// Example: Files that are automatically skipped
- document_anonymized.docx
- report_2024_anonymized.pdf  
- sensitive_data_anonymized.txt

// Files that are processed normally
- document.docx
- report_2024.pdf
- sensitive_data.txt
```

## Entity Objects

Entity objects are stored separately from reports and contain:

- `text`: The actual text content of the entity
- `entityType`: The type of entity (PERSON, ORGANIZATION, etc.)
- `occurrenceCount`: Number of times this entity has been detected
- `averageConfidence`: Average confidence score across all detections
- `firstDetected`: Timestamp of first detection
- `lastDetected`: Timestamp of last detection

## Entity Processing

When Presidio detects entities in a document, the system:

1. **Deduplicates entities** by text content (unique by text property)
2. **Finds or creates entity objects** for each unique entity
3. **Validates entity creation** to ensure objects have valid IDs
4. **Updates statistics** in the entity object with robust error handling
5. **Generates document-specific keys** for anonymization
6. **Adds anonymization flags** (default: true for security-first approach)
7. **Links entities to objects** via entityObjectId
8. **Provides fallback processing** if entity object operations fail

## Error Handling

The system includes comprehensive error handling:

- **Entity Creation Validation**: Verifies entity objects are created with valid IDs
- **Entity Not Found Handling**: Graceful fallback when entity lookups fail
- **Configuration Validation**: Ensures consistent app name usage across services
- **Fallback Processing**: Continues processing even if individual entities fail
- **Detailed Logging**: Comprehensive logging for debugging and monitoring

## Configuration Consistency

All services now use consistent configuration naming:
- App name: `'docudesk'` (lowercase) across all services
- Entity register: Configurable via `entity_register` setting
- Entity schema: Configurable via `entity_schema` setting

## Report Entity Structure

Entities in reports now include enhanced information:

- `text`: The detected text
- `score`: Confidence score from Presidio
- `entityType`: Type of entity detected
- `start` and `end`: Position in document
- `key`: Unique key for anonymization
- `anonymize`: Boolean flag for anonymization control
- `entityObjectId`: Reference to the centralized entity object (null if creation failed)

## Configuration

Entity management can be configured through the admin settings:

- **Entity Register**: Which register to use for storing entity objects (default: 'document')
- **Entity Schema**: Which schema to use for entity objects (default: 'entity')

## Anonymization Integration

Anonymization results are now stored directly on report objects in the `anonymization` property:

```json
{
  'anonymization': {
    'fileHash': 'abc123...',
    'anonymizedFileName': 'document_anonymized.docx',
    'anonymizedFilePath': '/path/to/anonymized/file',
    'replacements': [...],
    'startTime': 1234567890.123,
    'endTime': 1234567890.456,
    'processingTime': 0.333,
    'status': 'completed',
    'message': 'Anonymization completed successfully'
  }
}
```

## Entity Deduplication

The system automatically deduplicates entities by text content within each document, keeping the highest confidence score when duplicates are found.

## Privacy by Design

- Entities default to `anonymize: true` for security-first approach
- Individual entity anonymization can be toggled per document
- Entity objects track statistics without storing sensitive content context

## Benefits

1. **Consistency**: Same entities across documents are handled uniformly
2. **Efficiency**: Deduplication reduces storage and processing overhead  
3. **Intelligence**: Statistics help identify frequently occurring entities
4. **Control**: Fine-grained anonymization control per entity
5. **Tracking**: Complete audit trail of entity detections

## API Integration

The system integrates with:
- **Presidio**: For entity detection
- **OpenRegister**: For entity object storage
- **Reporting Service**: For enhanced entity processing
- **Anonymization Service**: For selective anonymization 