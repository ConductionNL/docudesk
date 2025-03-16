---
sidebar_position: 2
title: Privacy Data Tracking
---

# Privacy Data Tracking

DocuDesk provides robust privacy data tracking capabilities to help you maintain GDPR compliance. This page explains how DocuDesk tracks and manages privacy-related data in your documents.

## Overview

The Privacy Data Tracking system in DocuDesk helps you:

- Identify files containing personal data
- Categorize the types of personal data present
- Track anonymization status
- Manage retention periods
- Document the legal basis for processing
- Maintain an audit trail of privacy-related actions

## Privacy File Object

The `PrivacyFile` object is the core component for tracking privacy data. It contains metadata about a file's privacy status and GDPR compliance information.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique identifier for the privacy file record |
| file_id | string | Nextcloud file ID |
| file_name | string | Name of the file |
| file_path | string | Path to the file in Nextcloud |
| mime_type | string | MIME type of the file |
| contains_personal_data | boolean | Whether the file contains personal data |
| data_categories | array | Categories of personal data contained in the file |
| anonymization_status | string | Status of anonymization process |
| anonymization_date | date-time | Date when the file was anonymized |
| retention_period | integer | Retention period in days (0 for indefinite) |
| retention_expiry | date-time | Date when the retention period expires |
| legal_basis | string | Legal basis for processing the data under GDPR |
| data_controller | string | Name of the data controller |
| created_at | date-time | Creation timestamp |
| updated_at | date-time | Last update timestamp |

### Data Categories

DocuDesk recognizes the following categories of personal data:

- **name**: Names of individuals
- **address**: Physical addresses
- **email**: Email addresses
- **phone**: Phone numbers
- **id_number**: Identification numbers (passport, SSN, etc.)
- **financial**: Financial information (bank accounts, credit cards, etc.)
- **health**: Health-related information
- **biometric**: Biometric data
- **location**: Location data
- **other**: Other types of personal data

### Anonymization Status

The anonymization status can be one of the following:

- **not_required**: The file does not require anonymization
- **pending**: Anonymization is pending
- **in_progress**: Anonymization is in progress
- **completed**: Anonymization is completed
- **failed**: Anonymization failed

### Legal Basis

Under GDPR, personal data processing must have a legal basis. DocuDesk supports tracking the following legal bases:

- **consent**: The data subject has given consent
- **contract**: Processing is necessary for a contract
- **legal_obligation**: Processing is necessary for a legal obligation
- **vital_interests**: Processing is necessary to protect vital interests
- **public_interest**: Processing is necessary for a task in the public interest
- **legitimate_interests**: Processing is necessary for legitimate interests

## API Endpoints

DocuDesk provides the following API endpoints for managing privacy data:

### List Privacy Files

```
GET /apps/docudesk/api/v1/privacy/files
```

Returns a list of all files with privacy data.

### Get Privacy File

```
GET /apps/docudesk/api/v1/privacy/files/{fileId}
```

Returns the privacy data for a specific file.

### Update Privacy File

```
PUT /apps/docudesk/api/v1/privacy/files/{fileId}
```

Updates the privacy data for a specific file.

## Best Practices

1. **Regular Audits**: Regularly audit your files to identify those containing personal data
2. **Proper Categorization**: Accurately categorize the types of personal data in each file
3. **Anonymization**: Anonymize personal data when possible
4. **Retention Management**: Set appropriate retention periods and monitor expiry dates
5. **Legal Basis Documentation**: Always document the legal basis for processing personal data
6. **Data Controller Information**: Keep data controller information up to date

## Integration with Document Processing

The privacy tracking system integrates with DocuDesk's document processing capabilities:

- When a document is uploaded or created, it is automatically scanned for personal data
- If personal data is detected, a `PrivacyFile` record is created
- When a document is processed (e.g., for anonymization), the privacy record is updated
- The system enforces retention periods and can automatically notify you of expiring retention periods

For more information on document processing, see the [Document Processing](./document-processing.md) documentation. 