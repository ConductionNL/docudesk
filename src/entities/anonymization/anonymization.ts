/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

import { SafeParseReturnType, z } from 'zod'
import { TAnonymization } from './anonymization.types'

/**
 * Anonymization entity class
 * 
 * Represents an anonymization log for a document, tracking the anonymization process
 * and storing information about detected and replaced entities.
 * 
 * @see website/docs/api/anonymization-logs.md for documentation
 * 
 * @category Entity
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 * @link https://github.com/ConductionNL/docudesk
 */
export class Anonymization implements TAnonymization {
    /**
     * Unique identifier for the anonymization log
     */
    public readonly id: string;

    /**
     * Nextcloud node ID of the original document
     */
    public nodeId: string;

    /**
     * Hash of the file content
     */
    public fileHash: string;

    /**
     * Name of the document
     */
    public fileName: string;

    /**
     * Status of the anonymization operation
     */
    public status: 'pending' | 'processing' | 'completed' | 'failed';

    /**
     * Key used to de-anonymize the document (encrypted)
     */
    public anonymizationKey: string;

    /**
     * Original text of the document (stored securely)
     */
    public originalText: string;

    /**
     * Anonymized text of the document
     */
    public anonymizedText: string;

    /**
     * List of entity replacements made during anonymization
     */
    public entityReplacements: Array<{
        id: string;
        entityType: string;
        text: string;
        replacementText: string;
        score: number;
        startPosition: number;
        endPosition: number;
    }>;

    /**
     * List of entities found during anonymization
     */
    public entitiesFound: Array<{
        entityType: string;
        text: string;
        score: number;
        startPosition?: number;
        endPosition?: number;
    }>;

    /**
     * Total number of entities found
     */
    public totalEntitiesFound: number;

    /**
     * Total number of entities replaced
     */
    public totalEntitiesReplaced: number;

    /**
     * Nextcloud node ID of the anonymized document
     */
    public outputNodeId: string;

    /**
     * Confidence threshold used for entity detection
     */
    public confidenceThreshold: number;

    /**
     * Start time of the anonymization process
     */
    public startTime: string;

    /**
     * End time of the anonymization process
     */
    public endTime: string;

    /**
     * Duration of the anonymization process in milliseconds
     */
    public duration: number;

    /**
     * Error message if the anonymization failed
     */
    public errorMessage: string;

    /**
     * ID of the user who initiated the anonymization
     */
    public userId: string;

    /**
     * Creation timestamp
     */
    public created: string;

    /**
     * Last update timestamp
     */
    public updated: string;

    /**
     * Creates a new Anonymization instance
     * 
     * @param {TAnonymization} anonymization - Anonymization data
     */
    constructor(anonymization: TAnonymization) {
        this.id = anonymization.id || '';
        this.nodeId = anonymization.nodeId || '';
        this.fileHash = anonymization.fileHash || '';
        this.fileName = anonymization.fileName || '';
        this.status = anonymization.status || 'pending';
        this.anonymizationKey = anonymization.anonymizationKey || '';
        this.originalText = anonymization.originalText || '';
        this.anonymizedText = anonymization.anonymizedText || '';
        this.entityReplacements = anonymization.entityReplacements || [];
        this.entitiesFound = anonymization.entitiesFound || [];
        this.totalEntitiesFound = anonymization.totalEntitiesFound || 0;
        this.totalEntitiesReplaced = anonymization.totalEntitiesReplaced || 0;
        this.outputNodeId = anonymization.outputNodeId || '';
        this.confidenceThreshold = anonymization.confidenceThreshold || 0.7;
        this.startTime = anonymization.startTime || '';
        this.endTime = anonymization.endTime || '';
        this.duration = anonymization.duration || 0;
        this.errorMessage = anonymization.errorMessage || '';
        this.userId = anonymization.userId || '';
        this.created = anonymization.created || '';
        this.updated = anonymization.updated || '';
    }

    /**
     * Validates the anonymization data
     * 
     * @returns {SafeParseReturnType<TAnonymization, unknown>} Validation result
     */
    public validate(): SafeParseReturnType<TAnonymization, unknown> {
        const entityReplacementSchema = z.object({
            id: z.string(),
            entityType: z.string(),
            text: z.string(),
            replacementText: z.string(),
            score: z.number(),
            startPosition: z.number(),
            endPosition: z.number()
        });

        const entityFoundSchema = z.object({
            entityType: z.string(),
            text: z.string(),
            score: z.number(),
            startPosition: z.number().optional(),
            endPosition: z.number().optional()
        });

        const schema = z.object({
            id: z.string().optional(),
            nodeId: z.string().optional(),
            fileHash: z.string().optional(),
            fileName: z.string().optional(),
            status: z.enum(['pending', 'processing', 'completed', 'failed']).optional(),
            anonymizationKey: z.string().optional(),
            originalText: z.string().optional(),
            anonymizedText: z.string().optional(),
            entityReplacements: z.array(entityReplacementSchema).optional(),
            entitiesFound: z.array(entityFoundSchema).optional(),
            totalEntitiesFound: z.number().optional(),
            totalEntitiesReplaced: z.number().optional(),
            outputNodeId: z.string().optional(),
            confidenceThreshold: z.number().min(0).max(1).optional(),
            startTime: z.string().optional(),
            endTime: z.string().optional(),
            duration: z.number().optional(),
            errorMessage: z.string().optional(),
            userId: z.string().optional(),
            created: z.string().optional(),
            updated: z.string().optional()
        });

        return schema.safeParse(this);
    }

    /**
     * Gets the entity counts by type
     * 
     * @returns {Record<string, number>} Entity counts by type
     */
    public getEntityCounts(): Record<string, number> {
        const counts: Record<string, number> = {};
        
        this.entitiesFound.forEach(entity => {
            if (!counts[entity.entityType]) {
                counts[entity.entityType] = 0;
            }
            counts[entity.entityType]++;
        });
        
        return counts;
    }

    /**
     * Calculates the anonymization success rate
     * 
     * @returns {number} Success rate as a percentage
     */
    public getSuccessRate(): number {
        if (this.totalEntitiesFound === 0) {
            return 100;
        }
        
        return (this.totalEntitiesReplaced / this.totalEntitiesFound) * 100;
    }
} 