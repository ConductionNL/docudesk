---
sidebar_position: 6
title: Anonymization Logs
---

# Anonymization Logs

DocuDesk provides comprehensive anonymization capabilities to help you protect personal data in your documents. This page explains how anonymization logs work and how they can help you maintain GDPR compliance.

## Overview

The Anonymization Logs system in DocuDesk enables you to:

- Track all anonymization operations performed on documents
- Store detailed information about detected personal data
- Maintain a record of text replacements for potential de-anonymization
- Ensure GDPR compliance through proper documentation
- Generate audit trails for privacy-related actions

## Anonymization Log Object

The `AnonymizationLog` object is the core component for tracking document anonymization. It contains detailed information about the anonymization process, including the original text, anonymized text, and a secure key for de-anonymization.

### Key Properties

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique identifier for the log |
| node_id | string | Nextcloud node ID of the original document |
| file_hash | string | Hash of the file content |
| status | string | Status of the anonymization operation |
| anonymization_key | string | Key used to de-anonymize the document (encrypted) |
| original_text | string | Original text of the document (stored securely) |
| anonymized_text | string | Anonymized text of the document |
| entity_replacements | object | List of entity replacements made during anonymization |
| output_node_id | string | Nextcloud node ID of the anonymized document |

## Anonymization Process

When a document is submitted for anonymization, DocuDesk follows these steps:

1. **Document Analysis**: The document is analyzed to detect personal data
2. **Entity Detection**: Personal data entities are identified and categorized
3. **Replacement Generation**: Replacement text is generated for each entity
4. **Text Replacement**: The original text is replaced with anonymized text
5. **Key Generation**: A secure key is generated for potential de-anonymization
6. **Log Creation**: An anonymization log is created with all relevant information
7. **Output Generation**: An anonymized document is generated

## Entity Types

DocuDesk can detect and anonymize various types of personal data entities:

- **PERSON**: Names of individuals
- **LOCATION**: Physical locations (cities, countries, addresses)
- **ORGANIZATION**: Names of organizations
- **DATE**: Dates that could identify individuals
- **ID**: Identification numbers (passport, SSN, etc.)
- **EMAIL**: Email addresses
- **PHONE**: Phone numbers
- **ADDRESS**: Physical addresses
- **FINANCIAL**: Financial information (bank accounts, credit cards, etc.)
- **MEDICAL**: Medical information
- **OTHER**: Other types of personal data

## Presidio Integration

DocuDesk integrates with Microsoft Presidio for entity detection and anonymization. The response from Presidio is processed and stored in the anonymization log. Here's an example of a Presidio response:

```json
{
    "original_text": "Mijn naam is Jan de Vries, mijn BSN is 123456789 en ik woon in Amsterdam.",
    "anonymized_text": "Mijn naam is [PERSOON], mijn [PERSOON] is 123456789 en ik woon in [LOCATIE].",
    "entities_found": [
        {
            "entity_type": "PERSON",
            "text": "Jan de Vries",
            "score": 0.9999995231628418
        },
        {
            "entity_type": "LOCATION",
            "text": "Amsterdam",
            "score": 0.9999994039535522
        },
        {
            "entity_type": "PERSON",
            "text": "BSN",
            "score": 0.85
        }
    ]
}
```

This response is transformed into the `AnonymizationLog` object, with additional information such as:

- Unique identifiers for each replacement
- Positions of entities in the document
- A secure key for de-anonymization
- Metadata about the document and operation

## De-anonymization

DocuDesk supports de-anonymization of documents using the anonymization key. This feature is useful in scenarios where:

- The original document is needed for legal proceedings
- The anonymization was too aggressive and removed non-personal data
- The document needs to be shared with authorized parties who require the full information

De-anonymization is a secure process that requires:

1. The anonymized document (identified by its node ID)
2. The anonymization key (which is encrypted and stored securely)

## API Endpoints

DocuDesk provides the following API endpoints for managing anonymization logs:

### List Anonymization Logs

```
GET /apps/docudesk/api/v1/anonymization/logs
```

Returns a list of anonymization logs. You can filter the logs by:

- `node_id`: Filter logs by Nextcloud node ID
- `status`: Filter logs by status

### Create Anonymization Log

```
POST /apps/docudesk/api/v1/anonymization/logs
```

Creates a new anonymization log. You need to specify:

- `node_id`: Nextcloud node ID of the document to anonymize
- `file_name`: Name of the document
- `confidence_threshold`: (Optional) Confidence threshold for entity detection

### Get Anonymization Log

```
GET /apps/docudesk/api/v1/anonymization/logs/{logId}
```

Returns a specific anonymization log by ID.

### Update Anonymization Log

```
PUT /apps/docudesk/api/v1/anonymization/logs/{logId}
```

Updates a specific anonymization log.

### Get Latest Anonymization Log for Node

```
GET /apps/docudesk/api/v1/anonymization/logs/node/{nodeId}
```

Returns the latest anonymization log for a specific Nextcloud node.

### De-anonymize Document

```
POST /apps/docudesk/api/v1/anonymization/deanonymize
```

De-anonymizes a document using the anonymization key.

## Examples

### Anonymizing a Document

```php
// Create a new anonymization log
$logData = [
    'node_id' => '12345',
    'file_name' => 'sensitive-document.pdf',
    'confidence_threshold' => 0.7
];

$response = $client->post('/apps/docudesk/api/v1/anonymization/logs', [
    'json' => $logData
]);

$log = json_decode($response->getBody(), true);
$logId = $log['id'];

// Check anonymization status
$response = $client->get('/apps/docudesk/api/v1/anonymization/logs/' . $logId);
$log = json_decode($response->getBody(), true);

if ($log['status'] === 'completed') {
    // Get the anonymized document
    $anonymizedNodeId = $log['output_node_id'];
    
    // Store the anonymization key securely
    $anonymizationKey = $log['anonymization_key'];
    
    // Check anonymization results
    $totalEntitiesFound = $log['total_entities_found'];
    $totalEntitiesReplaced = $log['total_entities_replaced'];
    
    echo "Anonymization completed: Found $totalEntitiesFound entities, replaced $totalEntitiesReplaced entities.";
}
```

### De-anonymizing a Document

```php
// De-anonymize a document
$deanonymizeData = [
    'anonymized_node_id' => '67890',
    'anonymization_key' => 'secure-key-retrieved-from-storage'
];

$response = $client->post('/apps/docudesk/api/v1/anonymization/deanonymize', [
    'json' => $deanonymizeData
]);

$result = json_decode($response->getBody(), true);

if ($result['success']) {
    // Get the de-anonymized document
    $originalNodeId = $result['original_node_id'];
    echo "Document successfully de-anonymized. Original document ID: $originalNodeId";
}
```

## Best Practices

1. **Secure Key Storage**: Store anonymization keys securely, preferably in a separate system from the anonymized documents
2. **Regular Audits**: Regularly audit anonymization logs to ensure personal data is being properly protected
3. **Confidence Threshold**: Adjust the confidence threshold based on your needs (higher for more precision, lower for more coverage)
4. **Access Control**: Restrict access to de-anonymization functionality to authorized users only
5. **Retention Policy**: Implement a retention policy for anonymization logs in line with your GDPR compliance requirements

## Integration with Document Reports

The anonymization logs system integrates with DocuDesk's document reports:

- Anonymization results are included in document reports
- Document reports can trigger anonymization operations
- Anonymization logs provide detailed information for privacy compliance reporting

For more information on document reports, see the [Document Reports](./document-reports.md) documentation. 