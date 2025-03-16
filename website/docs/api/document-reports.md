---
sidebar_position: 5
title: Document Reports
---

# Document Reports

DocuDesk provides comprehensive document analysis through its reporting system. This page explains how document reports work and how they can help you ensure your documents meet privacy, accessibility, and readability standards.

## Overview

The Document Reports system in DocuDesk enables you to:

- Analyze documents for personal data that may require anonymization
- Check documents for WCAG accessibility compliance
- Assess the language level and readability of documents
- Track document changes through file hashing
- Generate detailed reports with actionable recommendations

## Document Report Object

The `DocumentReport` object is the core component for document analysis. It contains the results of various analyses performed on a document, including anonymization, WCAG compliance, and language level assessments.

### Key Properties

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique identifier for the report |
| node_id | string | Nextcloud node ID of the document |
| file_name | string | Name of the document |
| file_hash | string | Hash of the file content to determine if a new report is needed |
| status | string | Status of the report generation (pending, processing, completed, failed) |
| anonymization_results | object | Results of anonymization analysis |
| wcag_compliance_results | object | Results of WCAG compliance analysis |
| language_level_results | object | Results of language level analysis |
| created_at | date-time | When the report was created |
| updated_at | date-time | When the report was last updated |

## Report Generation Process

When a document is submitted for analysis, DocuDesk follows these steps:

1. **Hash Calculation**: Calculate a hash of the document content
2. **Check for Existing Reports**: Check if a report already exists for this document with the same hash
3. **Report Creation**: If no current report exists (or the hash is different), create a new report
4. **Analysis Queue**: Add the document to the analysis queue
5. **Processing**: Perform the requested analyses (anonymization, WCAG, language level)
6. **Report Update**: Update the report with the analysis results
7. **Notification**: Notify the user that the report is ready

## Analysis Types

### Anonymization Analysis

The anonymization analysis identifies personal data in documents that may need to be anonymized for GDPR compliance. It provides:

- Detection of various types of personal data (names, addresses, emails, etc.)
- Count and categorization of personal data instances
- Suggestions for anonymizing personal data
- Confidence scores for detected entities

### WCAG Compliance Analysis

The WCAG compliance analysis checks documents for accessibility issues according to the Web Content Accessibility Guidelines. It provides:

- Overall compliance level (A, AA, AAA, or non-compliant)
- Breakdown of issues by severity and WCAG principle
- Detailed list of accessibility issues with recommendations
- Overall compliance score

### Language Level Analysis

The language level analysis assesses the readability and complexity of document text. It provides:

- Primary language detection
- Various readability scores (Flesch-Kincaid, SMOG Index, etc.)
- Text complexity metrics
- Estimated education level required to understand the text
- Suggestions for improving language clarity

## API Endpoints

DocuDesk provides the following API endpoints for managing document reports:

### List Document Reports

```
GET /apps/docudesk/api/v1/reports
```

Returns a list of document reports. You can filter the reports by:

- `node_id`: Filter reports by Nextcloud node ID
- `status`: Filter reports by status

### Create Document Report

```
POST /apps/docudesk/api/v1/reports
```

Creates a new document report. You need to specify:

- `node_id`: Nextcloud node ID of the document
- `file_name`: Name of the document
- `file_hash`: Hash of the file content
- `analysis_types`: Types of analysis to perform (anonymization, wcag_compliance, language_level)

### Get Document Report

```
GET /apps/docudesk/api/v1/reports/{reportId}
```

Returns a specific document report by ID.

### Update Document Report

```
PUT /apps/docudesk/api/v1/reports/{reportId}
```

Updates a specific document report.

### Get Latest Report for Node

```
GET /apps/docudesk/api/v1/reports/node/{nodeId}
```

Returns the latest document report for a specific Nextcloud node.

## Use Cases

### GDPR Compliance

Document reports help ensure GDPR compliance by:

- Identifying documents containing personal data
- Suggesting anonymization methods for sensitive information
- Tracking anonymization status
- Providing an audit trail of privacy-related actions

### Accessibility Compliance

Document reports help ensure accessibility compliance by:

- Checking documents against WCAG standards
- Identifying accessibility issues
- Providing recommendations for fixing issues
- Tracking compliance levels over time

### Content Readability

Document reports help improve content readability by:

- Assessing the language level of documents
- Identifying complex language
- Suggesting simplifications
- Ensuring content is appropriate for the target audience

## Integration with Document Processing

The document reports system integrates with DocuDesk's document processing capabilities:

- Reports can trigger automatic document processing (e.g., anonymization)
- Processing results are reflected in updated reports
- Reports provide a history of document transformations

## Examples

### Generating a Document Report

```php
// Create a new document report
$reportData = [
    'node_id' => '12345',
    'file_name' => 'important-document.pdf',
    'file_hash' => 'a1b2c3d4e5f6g7h8i9j0',
    'analysis_types' => ['anonymization', 'wcag_compliance', 'language_level']
];

$response = $client->post('/apps/docudesk/api/v1/reports', [
    'json' => $reportData
]);

$report = json_decode($response->getBody(), true);
$reportId = $report['id'];

// Check report status
$response = $client->get('/apps/docudesk/api/v1/reports/' . $reportId);
$report = json_decode($response->getBody(), true);

if ($report['status'] === 'completed') {
    // Process report results
    $anonymizationResults = $report['anonymization_results'];
    $wcagResults = $report['wcag_compliance_results'];
    $languageResults = $report['language_level_results'];
    
    // Take action based on results
    if ($anonymizationResults['contains_personal_data']) {
        // Handle personal data
    }
    
    if ($wcagResults['compliance_level'] !== 'AA' && $wcagResults['compliance_level'] !== 'AAA') {
        // Address accessibility issues
    }
    
    if ($languageResults['education_level'] === 'graduate' || $languageResults['education_level'] === 'professional') {
        // Simplify language
    }
}
```

## Best Practices

1. **Regular Analysis**: Regularly analyze important documents to ensure continued compliance
2. **Hash-Based Updates**: Use file hashing to determine when documents have changed and need re-analysis
3. **Comprehensive Analysis**: Use all three analysis types for critical documents
4. **Action on Results**: Implement a workflow to address issues identified in reports
5. **Version Tracking**: Keep reports for different versions of documents to track improvements

## Conclusion

Document reports provide a powerful way to ensure your documents meet privacy, accessibility, and readability standards. By regularly analyzing documents and acting on the results, you can maintain compliance with regulations and improve the quality of your content. 