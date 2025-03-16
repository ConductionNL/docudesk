---
sidebar_position: 3
title: Parsing Logs
---

# Parsing Logs

DocuDesk maintains detailed logs of all document parsing operations. This page explains how parsing logs work and how to use them.

## Overview

The Parsing Logs system in DocuDesk helps you:

- Track all document processing operations
- Monitor the status of ongoing operations
- Troubleshoot failed operations
- Maintain an audit trail of document transformations
- Analyze performance metrics

## Parsing Log Object

The `ParsingLog` object is the core component for tracking document parsing operations. It contains metadata about a parsing operation, including its status, duration, and results.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique identifier for the parsing log |
| file_id | string | Nextcloud file ID of the parsed file |
| file_name | string | Name of the parsed file |
| file_path | string | Path to the file in Nextcloud |
| mime_type | string | MIME type of the file |
| parsing_type | string | Type of parsing operation performed |
| status | string | Status of the parsing operation |
| started_at | date-time | When the parsing operation started |
| completed_at | date-time | When the parsing operation completed |
| duration_ms | integer | Duration of the parsing operation in milliseconds |
| result_summary | string | Summary of the parsing result |
| error_message | string | Error message if the parsing failed |
| output_file_id | string | ID of the output file (if any) |
| output_format | string | Format of the output file |
| user_id | string | ID of the user who initiated the parsing |
| created_at | date-time | Creation timestamp |
| updated_at | date-time | Last update timestamp |

### Parsing Types

DocuDesk supports the following types of parsing operations:

- **text_extraction**: Extracting text content from documents
- **metadata_extraction**: Extracting metadata from documents
- **anonymization**: Anonymizing personal data in documents
- **format_conversion**: Converting documents between formats
- **accessibility_check**: Checking documents for accessibility compliance
- **validation**: Validating documents against templates or schemas

### Status Values

A parsing operation can have one of the following statuses:

- **pending**: The operation is queued but not yet started
- **processing**: The operation is in progress
- **completed**: The operation completed successfully
- **failed**: The operation failed

## API Endpoints

DocuDesk provides the following API endpoints for managing parsing logs:

### List Parsing Logs

```
GET /apps/docudesk/api/v1/parsing/logs
```

Returns a list of parsing logs. You can filter the logs by:

- `file_id`: Filter logs by file ID
- `status`: Filter logs by parsing status
- `from_date`: Filter logs from this date
- `to_date`: Filter logs until this date

### Create Parsing Log

```
POST /apps/docudesk/api/v1/parsing/logs
```

Creates a new parsing log entry for a file.

### Get Parsing Log

```
GET /apps/docudesk/api/v1/parsing/logs/{logId}
```

Returns a specific parsing log by ID.

### Update Parsing Log

```
PUT /apps/docudesk/api/v1/parsing/logs/{logId}
```

Updates a specific parsing log entry.

## Use Cases

### Monitoring Document Processing

Parsing logs allow you to monitor the status of document processing operations in real-time. You can:

- Check if a document is currently being processed
- See how long a processing operation is taking
- Identify bottlenecks in your document processing workflow

### Troubleshooting

When a document processing operation fails, the parsing log contains valuable information for troubleshooting:

- Error messages explaining why the operation failed
- The type of operation that was attempted
- The format of the input and output files

### Audit Trail

Parsing logs provide a complete audit trail of all document transformations:

- Who initiated each operation
- When each operation occurred
- What type of operation was performed
- The result of each operation

### Performance Analysis

By analyzing parsing logs, you can gain insights into the performance of your document processing operations:

- Average processing time for different types of documents
- Success rates for different operations
- Trends in document processing volume

## Integration with Privacy Tracking

The parsing logs system integrates with DocuDesk's privacy tracking capabilities:

- When a document is processed for anonymization, both a parsing log and a privacy record are updated
- You can trace the history of anonymization attempts for a file
- You can verify that personal data has been properly anonymized

For more information on privacy tracking, see the [Privacy Data Tracking](./privacy-tracking.md) documentation. 