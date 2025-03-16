/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

import { describe, it, expect } from 'vitest'
import { Report } from './report'

describe('Report Entity', () => {
    it('creates a report instance with default values', () => {
        const report = new Report({})
        expect(report.id).toBe('')
        expect(report.nodeId).toBe('')
        expect(report.status).toBe('pending')
        expect(report.retentionPeriod).toBe(0)
        expect(report.dataController).toBe('')
    })

    it('creates a report instance with provided values', () => {
        const data = {
            id: '123',
            nodeId: '456',
            fileName: 'test.pdf',
            fileHash: 'abc123',
            status: 'completed',
            anonymizationResults: {
                containsPersonalData: true,
                entitiesFound: [
                    {
                        entityType: 'PERSON',
                        text: 'John Doe',
                        score: 0.95,
                        count: 1
                    }
                ],
                totalEntitiesFound: 1,
                dataCategories: ['name'],
                anonymizationStatus: 'pending',
                anonymizationLogId: '789'
            },
            wcagComplianceResults: {
                complianceLevel: 'AA',
                complianceScore: 85,
                issues: [
                    {
                        principle: 'Perceivable',
                        guideline: '1.1',
                        criterion: '1.1.1',
                        severity: 'error',
                        message: 'Image missing alt text',
                        element: 'img',
                        recommendation: 'Add alt text to image'
                    }
                ],
                totalIssues: 1,
                issuesBySeverity: {
                    error: 1,
                    warning: 0,
                    notice: 0
                }
            },
            languageLevelResults: {
                primaryLanguage: 'en',
                readabilityScores: {
                    fleschKincaid: 8.5,
                    smogIndex: 7.2
                },
                educationLevel: 'high_school',
                textComplexity: 'moderate',
                suggestions: ['Simplify language']
            },
            retentionPeriod: 365,
            retentionExpiry: '2025-01-01T00:00:00Z',
            legalBasis: 'consent',
            dataController: 'Example Corp'
        }
        
        const report = new Report(data)
        expect(report.id).toBe('123')
        expect(report.nodeId).toBe('456')
        expect(report.fileName).toBe('test.pdf')
        expect(report.fileHash).toBe('abc123')
        expect(report.status).toBe('completed')
        expect(report.anonymizationResults).toBeDefined()
        expect(report.anonymizationResults.containsPersonalData).toBe(true)
        expect(report.anonymizationResults.entitiesFound).toHaveLength(1)
        expect(report.wcagComplianceResults).toBeDefined()
        expect(report.wcagComplianceResults.complianceLevel).toBe('AA')
        expect(report.languageLevelResults).toBeDefined()
        expect(report.languageLevelResults.educationLevel).toBe('high_school')
        expect(report.retentionPeriod).toBe(365)
        expect(report.retentionExpiry).toBe('2025-01-01T00:00:00Z')
        expect(report.legalBasis).toBe('consent')
        expect(report.dataController).toBe('Example Corp')
    })

    it('validates a valid report object', () => {
        const data = {
            id: '123',
            nodeId: '456',
            status: 'completed',
            anonymizationResults: {
                containsPersonalData: true,
                entitiesFound: [],
                totalEntitiesFound: 0,
                dataCategories: [],
                anonymizationStatus: 'not_required'
            }
        }
        
        const report = new Report(data)
        const result = report.validate()
        expect(result.success).toBe(true)
    })

    it('validates an invalid report object', () => {
        const data = {
            id: '123',
            nodeId: '456',
            status: 'invalid_status', // Invalid status
            legalBasis: 'invalid_basis' // Invalid legal basis
        }
        
        const report = new Report(data)
        const result = report.validate()
        expect(result.success).toBe(false)
    })

    it('checks if document contains personal data', () => {
        const report1 = new Report({
            anonymizationResults: {
                containsPersonalData: true,
                entitiesFound: [],
                totalEntitiesFound: 0,
                dataCategories: [],
                anonymizationStatus: 'pending'
            }
        })
        
        const report2 = new Report({
            anonymizationResults: {
                containsPersonalData: false,
                entitiesFound: [],
                totalEntitiesFound: 0,
                dataCategories: [],
                anonymizationStatus: 'not_required'
            }
        })
        
        const report3 = new Report({})
        
        expect(report1.containsPersonalData()).toBe(true)
        expect(report2.containsPersonalData()).toBe(false)
        expect(report3.containsPersonalData()).toBe(false)
    })

    it('gets data categories', () => {
        const report = new Report({
            anonymizationResults: {
                containsPersonalData: true,
                entitiesFound: [],
                totalEntitiesFound: 0,
                dataCategories: ['name', 'email', 'phone'],
                anonymizationStatus: 'pending'
            }
        })
        
        expect(report.getDataCategories()).toEqual(['name', 'email', 'phone'])
    })

    it('gets compliance level', () => {
        const report1 = new Report({
            wcagComplianceResults: {
                complianceLevel: 'AA',
                complianceScore: 85,
                issues: [],
                totalIssues: 0,
                issuesBySeverity: {
                    error: 0,
                    warning: 0,
                    notice: 0
                }
            }
        })
        
        const report2 = new Report({})
        
        expect(report1.getComplianceLevel()).toBe('AA')
        expect(report2.getComplianceLevel()).toBe('unknown')
    })

    it('gets education level', () => {
        const report1 = new Report({
            languageLevelResults: {
                primaryLanguage: 'en',
                readabilityScores: {},
                educationLevel: 'college',
                textComplexity: 'difficult',
                suggestions: []
            }
        })
        
        const report2 = new Report({})
        
        expect(report1.getEducationLevel()).toBe('college')
        expect(report2.getEducationLevel()).toBe('unknown')
    })

    it('checks if retention period has expired', () => {
        const pastDate = new Date()
        pastDate.setDate(pastDate.getDate() - 10)
        
        const futureDate = new Date()
        futureDate.setDate(futureDate.getDate() + 10)
        
        const report1 = new Report({
            retentionExpiry: pastDate.toISOString()
        })
        
        const report2 = new Report({
            retentionExpiry: futureDate.toISOString()
        })
        
        const report3 = new Report({})
        
        expect(report1.isRetentionExpired()).toBe(true)
        expect(report2.isRetentionExpired()).toBe(false)
        expect(report3.isRetentionExpired()).toBe(false)
    })
}) 