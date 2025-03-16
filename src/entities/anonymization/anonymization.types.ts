/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

/**
 * Type definition for Anonymization entity
 * 
 * @see website/docs/api/anonymization-logs.md for documentation
 */
export type TAnonymization = {
    id?: string
    nodeId?: string
    fileHash?: string
    fileName?: string
    status?: 'pending' | 'processing' | 'completed' | 'failed'
    anonymizationKey?: string
    originalText?: string
    anonymizedText?: string
    entityReplacements?: Array<{
        id: string
        entityType: string
        text: string
        replacementText: string
        score: number
        startPosition: number
        endPosition: number
    }>
    entitiesFound?: Array<{
        entityType: string
        text: string
        score: number
        startPosition?: number
        endPosition?: number
    }>
    totalEntitiesFound?: number
    totalEntitiesReplaced?: number
    outputNodeId?: string
    confidenceThreshold?: number
    startTime?: string
    endTime?: string
    duration?: number
    errorMessage?: string
    userId?: string
    created?: string
    updated?: string
} 