/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

import { SafeParseReturnType, z } from 'zod'
import { TReport } from './report.types'

/**
 * Report entity class
 * 
 * Represents a document report containing analysis results for anonymization,
 * WCAG compliance, and language level assessments.
 * 
 * @see website/docs/api/document-reports.md for documentation
 * 
 * @category Entity
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 * @link https://github.com/ConductionNL/docudesk
 */
export class Report implements TReport {
    /**
     * Unique identifier for the report
     */
    public readonly id: string;

    /**
     * Nextcloud node ID of the document
     */
    public nodeId: string;

    /**
     * Name of the document
     */
    public fileName: string;

    /**
     * Hash of the file content to determine if a new report is needed
     */
    public fileHash: string;

    /**
     * Status of the report generation
     */
    public status: 'pending' | 'processing' | 'completed' | 'failed';

    /**
     * Results of anonymization analysis
     */
    public anonymizationResults?: {
        containsPersonalData: boolean;
        entitiesFound: Array<{
            entityType: string;
            text: string;
            score: number;
            count: number;
        }>;
        totalEntitiesFound: number;
        dataCategories: Array<string>;
        anonymizationStatus: 'not_required' | 'pending' | 'in_progress' | 'completed' | 'failed';
        anonymizationLogId?: string;
    };

    /**
     * Results of WCAG compliance analysis
     */
    public wcagComplianceResults?: {
        complianceLevel: 'A' | 'AA' | 'AAA' | 'non-compliant';
        complianceScore: number;
        issues: Array<{
            principle: string;
            guideline: string;
            criterion: string;
            severity: 'error' | 'warning' | 'notice';
            message: string;
            element?: string;
            recommendation?: string;
        }>;
        totalIssues: number;
        issuesBySeverity: {
            error: number;
            warning: number;
            notice: number;
        };
    };

    /**
     * Results of language level analysis
     */
    public languageLevelResults?: {
        primaryLanguage: string;
        readabilityScores: {
            fleschKincaid?: number;
            smogIndex?: number;
            colemanLiau?: number;
            automatedReadability?: number;
            daleChall?: number;
        };
        educationLevel: 'elementary' | 'middle_school' | 'high_school' | 'college' | 'graduate' | 'professional';
        textComplexity: 'very_easy' | 'easy' | 'moderate' | 'difficult' | 'very_difficult';
        suggestions: Array<string>;
    };

    /**
     * Retention period in days (0 for indefinite)
     */
    public retentionPeriod: number;

    /**
     * Date when the retention period expires
     */
    public retentionExpiry: string;

    /**
     * Legal basis for processing the data under GDPR
     */
    public legalBasis?: 'consent' | 'contract' | 'legal_obligation' | 'vital_interests' | 'public_interest' | 'legitimate_interests';

    /**
     * Name of the data controller
     */
    public dataController: string;

    /**
     * Start time of the report generation process
     */
    public startTime: string;

    /**
     * End time of the report generation process
     */
    public endTime: string;

    /**
     * Duration of the report generation process in milliseconds
     */
    public duration: number;

    /**
     * Error message if the report generation failed
     */
    public errorMessage: string;

    /**
     * ID of the user who initiated the report generation
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
     * Creates a new Report instance
     * 
     * @param {TReport} report - Report data
     */
    constructor(report: TReport) {
        this.id = report.id || '';
        this.nodeId = report.nodeId || '';
        this.fileName = report.fileName || '';
        this.fileHash = report.fileHash || '';
        this.status = report.status || 'pending';
        this.anonymizationResults = report.anonymizationResults;
        this.wcagComplianceResults = report.wcagComplianceResults;
        this.languageLevelResults = report.languageLevelResults;
        this.retentionPeriod = report.retentionPeriod || 0;
        this.retentionExpiry = report.retentionExpiry || '';
        this.legalBasis = report.legalBasis;
        this.dataController = report.dataController || '';
        this.startTime = report.startTime || '';
        this.endTime = report.endTime || '';
        this.duration = report.duration || 0;
        this.errorMessage = report.errorMessage || '';
        this.userId = report.userId || '';
        this.created = report.created || '';
        this.updated = report.updated || '';
    }

    /**
     * Validates the report data
     * 
     * @returns {SafeParseReturnType<TReport, unknown>} Validation result
     */
    public validate(): SafeParseReturnType<TReport, unknown> {
        const entityFoundSchema = z.object({
            entityType: z.string(),
            text: z.string(),
            score: z.number(),
            count: z.number()
        });

        const issueSchema = z.object({
            principle: z.string(),
            guideline: z.string(),
            criterion: z.string(),
            severity: z.enum(['error', 'warning', 'notice']),
            message: z.string(),
            element: z.string().optional(),
            recommendation: z.string().optional()
        });

        const anonymizationResultsSchema = z.object({
            containsPersonalData: z.boolean(),
            entitiesFound: z.array(entityFoundSchema),
            totalEntitiesFound: z.number(),
            dataCategories: z.array(z.string()),
            anonymizationStatus: z.enum(['not_required', 'pending', 'in_progress', 'completed', 'failed']),
            anonymizationLogId: z.string().optional()
        }).optional();

        const wcagComplianceResultsSchema = z.object({
            complianceLevel: z.enum(['A', 'AA', 'AAA', 'non-compliant']),
            complianceScore: z.number(),
            issues: z.array(issueSchema),
            totalIssues: z.number(),
            issuesBySeverity: z.object({
                error: z.number(),
                warning: z.number(),
                notice: z.number()
            })
        }).optional();

        const readabilityScoresSchema = z.object({
            fleschKincaid: z.number().optional(),
            smogIndex: z.number().optional(),
            colemanLiau: z.number().optional(),
            automatedReadability: z.number().optional(),
            daleChall: z.number().optional()
        });

        const languageLevelResultsSchema = z.object({
            primaryLanguage: z.string(),
            readabilityScores: readabilityScoresSchema,
            educationLevel: z.enum(['elementary', 'middle_school', 'high_school', 'college', 'graduate', 'professional']),
            textComplexity: z.enum(['very_easy', 'easy', 'moderate', 'difficult', 'very_difficult']),
            suggestions: z.array(z.string())
        }).optional();

        const schema = z.object({
            id: z.string().optional(),
            nodeId: z.string().optional(),
            fileName: z.string().optional(),
            fileHash: z.string().optional(),
            status: z.enum(['pending', 'processing', 'completed', 'failed']).optional(),
            anonymizationResults: anonymizationResultsSchema,
            wcagComplianceResults: wcagComplianceResultsSchema,
            languageLevelResults: languageLevelResultsSchema,
            retentionPeriod: z.number().optional(),
            retentionExpiry: z.string().optional(),
            legalBasis: z.enum(['consent', 'contract', 'legal_obligation', 'vital_interests', 'public_interest', 'legitimate_interests']).optional(),
            dataController: z.string().optional(),
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
     * Checks if the document contains personal data
     * 
     * @returns {boolean} True if the document contains personal data
     */
    public containsPersonalData(): boolean {
        return this.anonymizationResults?.containsPersonalData || false;
    }

    /**
     * Gets the data categories found in the document
     * 
     * @returns {Array<string>} Array of data categories
     */
    public getDataCategories(): Array<string> {
        return this.anonymizationResults?.dataCategories || [];
    }

    /**
     * Gets the WCAG compliance level
     * 
     * @returns {string} WCAG compliance level
     */
    public getComplianceLevel(): string {
        return this.wcagComplianceResults?.complianceLevel || 'unknown';
    }

    /**
     * Gets the education level required to understand the document
     * 
     * @returns {string} Education level
     */
    public getEducationLevel(): string {
        return this.languageLevelResults?.educationLevel || 'unknown';
    }

    /**
     * Checks if the retention period has expired
     * 
     * @returns {boolean} True if the retention period has expired
     */
    public isRetentionExpired(): boolean {
        if (!this.retentionExpiry) {
            return false;
        }
        
        const expiryDate = new Date(this.retentionExpiry);
        const currentDate = new Date();
        
        return expiryDate < currentDate;
    }
} 