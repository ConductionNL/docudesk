/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

/**
 * Type definition for Report entity
 * 
 * @see website/docs/api/document-reports.md for documentation
 */
export type TReport = {
    id?: string
    nodeId?: string
    fileName?: string
    fileHash?: string
    status?: 'pending' | 'processing' | 'completed' | 'failed'
    anonymizationResults?: {
        containsPersonalData: boolean
        entitiesFound: Array<{
            entityType: string
            text: string
            score: number
            count: number
        }>
        totalEntitiesFound: number
        dataCategories: Array<string>
        anonymizationStatus: 'not_required' | 'pending' | 'in_progress' | 'completed' | 'failed'
        anonymizationLogId?: string
    }
    wcagComplianceResults?: {
        complianceLevel: 'A' | 'AA' | 'AAA' | 'non-compliant'
        complianceScore: number
        issues: Array<{
            principle: string
            guideline: string
            criterion: string
            severity: 'error' | 'warning' | 'notice'
            message: string
            element?: string
            recommendation?: string
        }>
        totalIssues: number
        issuesBySeverity: {
            error: number
            warning: number
            notice: number
        }
    }
    languageLevelResults?: {
        primaryLanguage: string
        readabilityScores: {
            fleschKincaid?: number
            smogIndex?: number
            colemanLiau?: number
            automatedReadability?: number
            daleChall?: number
        }
        educationLevel: 'elementary' | 'middle_school' | 'high_school' | 'college' | 'graduate' | 'professional'
        textComplexity: 'very_easy' | 'easy' | 'moderate' | 'difficult' | 'very_difficult'
        suggestions: Array<string>
    }
    retentionPeriod?: number
    retentionExpiry?: string
    legalBasis?: 'consent' | 'contract' | 'legal_obligation' | 'vital_interests' | 'public_interest' | 'legitimate_interests'
    dataController?: string
    startTime?: string
    endTime?: string
    duration?: number
    errorMessage?: string
    userId?: string
    created?: string
    updated?: string
} 