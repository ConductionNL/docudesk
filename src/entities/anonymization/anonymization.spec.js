/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

import { describe, it, expect } from 'vitest'
import { Anonymization } from './anonymization'

describe('Anonymization Entity', () => {
    it('creates an anonymization instance with default values', () => {
        const anonymization = new Anonymization({})
        expect(anonymization.id).toBe('')
        expect(anonymization.nodeId).toBe('')
        expect(anonymization.status).toBe('pending')
        expect(anonymization.entityReplacements).toEqual([])
        expect(anonymization.entitiesFound).toEqual([])
        expect(anonymization.totalEntitiesFound).toBe(0)
        expect(anonymization.totalEntitiesReplaced).toBe(0)
    })

    it('creates an anonymization instance with provided values', () => {
        const data = {
            id: '123',
            nodeId: '456',
            fileName: 'test.pdf',
            status: 'completed',
            anonymizationKey: 'secret-key',
            originalText: 'Original text with personal data',
            anonymizedText: 'Anonymized text with [PERSON]',
            entityReplacements: [
                {
                    id: 'rep1',
                    entityType: 'PERSON',
                    text: 'John Doe',
                    replacementText: '[PERSON]',
                    score: 0.95,
                    startPosition: 10,
                    endPosition: 18
                }
            ],
            entitiesFound: [
                {
                    entityType: 'PERSON',
                    text: 'John Doe',
                    score: 0.95,
                    startPosition: 10,
                    endPosition: 18
                }
            ],
            totalEntitiesFound: 1,
            totalEntitiesReplaced: 1,
            outputNodeId: '789',
            confidenceThreshold: 0.8
        }
        
        const anonymization = new Anonymization(data)
        expect(anonymization.id).toBe('123')
        expect(anonymization.nodeId).toBe('456')
        expect(anonymization.fileName).toBe('test.pdf')
        expect(anonymization.status).toBe('completed')
        expect(anonymization.anonymizationKey).toBe('secret-key')
        expect(anonymization.originalText).toBe('Original text with personal data')
        expect(anonymization.anonymizedText).toBe('Anonymized text with [PERSON]')
        expect(anonymization.entityReplacements).toHaveLength(1)
        expect(anonymization.entityReplacements[0].entityType).toBe('PERSON')
        expect(anonymization.entitiesFound).toHaveLength(1)
        expect(anonymization.entitiesFound[0].text).toBe('John Doe')
        expect(anonymization.totalEntitiesFound).toBe(1)
        expect(anonymization.totalEntitiesReplaced).toBe(1)
        expect(anonymization.outputNodeId).toBe('789')
        expect(anonymization.confidenceThreshold).toBe(0.8)
    })

    it('validates a valid anonymization object', () => {
        const data = {
            id: '123',
            nodeId: '456',
            status: 'completed',
            entityReplacements: [
                {
                    id: 'rep1',
                    entityType: 'PERSON',
                    text: 'John Doe',
                    replacementText: '[PERSON]',
                    score: 0.95,
                    startPosition: 10,
                    endPosition: 18
                }
            ],
            confidenceThreshold: 0.7
        }
        
        const anonymization = new Anonymization(data)
        const result = anonymization.validate()
        expect(result.success).toBe(true)
    })

    it('validates an invalid anonymization object', () => {
        const data = {
            id: '123',
            nodeId: '456',
            status: 'invalid_status', // Invalid status
            confidenceThreshold: 2.0 // Invalid threshold (> 1)
        }
        
        const anonymization = new Anonymization(data)
        const result = anonymization.validate()
        expect(result.success).be.false
    })

    it('gets entity counts by type', () => {
        const anonymization = new Anonymization({
            entitiesFound: [
                { entityType: 'PERSON', text: 'John Doe', score: 0.95 },
                { entityType: 'PERSON', text: 'Jane Smith', score: 0.92 },
                { entityType: 'EMAIL', text: 'john@example.com', score: 0.98 },
                { entityType: 'LOCATION', text: 'New York', score: 0.90 }
            ]
        })
        
        const counts = anonymization.getEntityCounts()
        expect(counts.PERSON).toBe(2)
        expect(counts.EMAIL).toBe(1)
        expect(counts.LOCATION).toBe(1)
    })

    it('calculates success rate correctly', () => {
        const anonymization = new Anonymization({
            totalEntitiesFound: 10,
            totalEntitiesReplaced: 8
        })
        
        expect(anonymization.getSuccessRate()).toBe(80)
    })

    it('returns 100% success rate when no entities found', () => {
        const anonymization = new Anonymization({
            totalEntitiesFound: 0,
            totalEntitiesReplaced: 0
        })
        
        expect(anonymization.getSuccessRate()).toBe(100)
    })
}) 