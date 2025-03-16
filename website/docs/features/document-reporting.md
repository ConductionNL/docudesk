---
sidebar_position: 9
---

# Document Reporting

DocuDesk's document reporting feature provides comprehensive analysis of documents to identify sensitive information and assess potential privacy risks. This feature integrates with Microsoft Presidio to detect and report on personally identifiable information (PII) and other sensitive data within your documents.

## Overview

The document reporting system:

1. Extracts text from various document formats
2. Analyzes the text for sensitive information using Presidio
3. Generates detailed reports with risk assessments
4. Stores reports for future reference and compliance purposes

## Key Features

- **Entity Detection**: Identifies various types of sensitive information (names, emails, credit cards, etc.)
- **Risk Scoring**: Calculates risk scores based on the type and quantity of sensitive data
- **Detailed Reports**: Provides comprehensive reports with entity counts and risk levels
- **Metadata Analysis**: Includes document metadata in the analysis
- **Historical Tracking**: Maintains a history of document analyses for compliance

## Supported Entity Types

The reporting system can detect various types of sensitive information, including:

- Personal names
- Email addresses
- Phone numbers
- Credit card numbers
- Bank account numbers
- Social security numbers
- Addresses and locations
- Dates of birth
- IP addresses
- Medical license numbers
- Passport numbers
- Driver's license numbers

## Risk Assessment

Each report includes a risk assessment with:

- **Risk Score**: A numerical score (0-100) indicating the overall risk level
- **Risk Level**: A categorical assessment (Low, Medium, High, Critical)
- **Entity Counts**: Breakdown of detected entities by type
- **Context Information**: Document metadata and processing details

## Using Document Reporting

You can generate reports programmatically:

```php
// Example: Generate a report for a document
$reportingService = \OC::$server->get(OCA\DocuDesk\Service\ReportingService::class);
$report = $reportingService->generateReport('/path/to/document.pdf', 'doc-123', 'Important Contract');

// Example: Retrieve a previously generated report
$report = $reportingService->getReport('report-id');

// Example: Get all reports for a document
$reports = $reportingService->getReports('doc-123');
```

## Integration with Presidio

The reporting feature integrates with Microsoft Presidio, an open-source PII detection service:

- Sends extracted text to Presidio for analysis
- Configurable confidence threshold for entity detection
- Customizable entity types and detection rules
- Support for multiple languages (depending on Presidio configuration)

## Configuration

Configure the reporting feature in the DocuDesk admin settings:

1. Navigate to **Admin Settings** > **DocuDesk**
2. Set the **Presidio API URL** (default: http://presidio-api:8080/analyze)
3. Adjust the **Confidence Threshold** (0.0-1.0) for entity detection sensitivity
4. Enable or disable the reporting feature

## Setting Up Presidio

To use the reporting feature, you need to set up Microsoft Presidio:

1. Deploy Presidio using Docker or Kubernetes (see [Presidio documentation](https://microsoft.github.io/presidio/))
2. Configure the analyzer service with appropriate recognition models
3. Update the DocuDesk settings with your Presidio API URL

## Performance Considerations

Document reporting can be resource-intensive:

- Process large documents asynchronously
- Consider batching multiple documents for analysis
- Implement caching for frequently accessed reports
- Monitor Presidio resource usage for large-scale deployments

## Security and Privacy

The reporting feature is designed with security in mind:

- All communication with Presidio is secured
- Reports are stored securely within your Nextcloud instance
- Access to reports can be restricted based on user permissions
- No sensitive data is sent to external services beyond Presidio

## Compliance Use Cases

Document reporting supports various compliance scenarios:

- **GDPR Compliance**: Identify documents containing personal data
- **PCI DSS**: Detect credit card information in documents
- **HIPAA**: Identify documents with protected health information
- **Data Minimization**: Support data minimization efforts by identifying unnecessary PII
- **Data Mapping**: Help create data maps by identifying where sensitive data resides

## Limitations

Be aware of these limitations:

- Detection accuracy depends on Presidio's recognition capabilities
- Some context-specific PII may not be detected without custom recognizers
- Very large documents may require additional processing time
- Image-based documents require OCR before analysis (not included) 