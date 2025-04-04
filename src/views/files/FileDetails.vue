<script setup>
import { reportStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="head">
					<h1 class="h1">
						{{ reportStore.reportItem.fileName }}
					</h1>
					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editReport')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit Report
						</NcActionButton>
					</NcActions>
				</div>

				<!-- Status Badge -->
				<div class="status-badge-container">
					<NcBadge 
						:type="getStatusBadgeType(reportStore.reportItem.status)" 
						class="status-badge">
						{{ reportStore.reportItem.status }}
					</NcBadge>
					<NcBadge 
						v-if="reportStore.reportItem.riskLevel" 
						:type="getRiskLevelBadgeType(reportStore.reportItem.riskLevel)" 
						class="risk-badge">
						Risk: {{ reportStore.reportItem.riskLevel }}
					</NcBadge>
				</div>

				<!-- Error Message -->
				<NcNoteCard v-if="reportStore.reportItem.errorMessage" type="error">
					{{ reportStore.reportItem.errorMessage }}
				</NcNoteCard>

				<!-- Risk Assessment Section -->
				<div class="section-container">
					<h2>Risk Assessment</h2>
					<div class="risk-score-container">
						<div class="risk-score-circle" :class="getRiskScoreClass(reportStore.reportItem.riskScore)">
							<span class="risk-score-value">{{ formatRiskScore(reportStore.reportItem.riskScore) }}</span>
						</div>
						<div class="risk-score-details">
							<h3>Risk Level: <span class="risk-level-badge" :class="getRiskLevelClass(reportStore.reportItem.riskLevel)">{{ reportStore.reportItem.riskLevel }}</span></h3>
							<p class="risk-explanation">
								{{ getRiskExplanation(reportStore.reportItem.riskLevel) }}
							</p>
						</div>
					</div>
					
					<div v-if="reportStore.reportItem.entities && reportStore.reportItem.entities.length > 0" class="risk-factors">
						<h3>Risk Factors</h3>
						<p>The risk assessment is based on {{ reportStore.reportItem.entities.length }} detected entities:</p>
						<ul class="risk-factors-list">
							<li v-for="(entityType, index) in getEntityTypes(reportStore.reportItem.entities)" :key="index">
								<strong>{{ formatEntityType(entityType.type) }}:</strong> {{ entityType.count }} occurrences
								<span class="entity-weight">(Weight: {{ getEntityWeight(entityType.type) }})</span>
							</li>
						</ul>
						<p class="risk-calculation-note">
							Risk score is calculated based on entity types, their confidence scores, and the total number of entities found.
							Higher weights are assigned to more sensitive data types like credit card numbers and personal identifiers.
						</p>
					</div>
					<div v-else class="empty-state">
						<p>No risk factors detected in this document.</p>
					</div>
				</div>

				<!-- Tabs for different analysis results -->
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<!-- File Information Tab (Default) -->
						<BTab title="File Information" active>
							<div class="detail-grid">
								<div class="detail-item">
									<span class="detail-label">File Path:</span>
									<span class="detail-value">{{ reportStore.reportItem.filePath }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">File Type:</span>
									<span class="detail-value">{{ reportStore.reportItem.fileType }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">File Extension:</span>
									<span class="detail-value">{{ reportStore.reportItem.fileExtension }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">File Size:</span>
									<span class="detail-value">{{ formatFileSize(reportStore.reportItem.fileSize) }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">File Hash:</span>
									<span class="detail-value">{{ reportStore.reportItem.fileHash }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">Node ID:</span>
									<span class="detail-value">{{ reportStore.reportItem.nodeId }}</span>
								</div>
							</div>
						</BTab>
						
						<!-- Entities Tab -->
						<BTab title="Entities">
							<div v-if="reportStore.reportItem.entities && reportStore.reportItem.entities.length > 0">
								<div class="entity-list">
									<div v-for="(entity, index) in reportStore.reportItem.entities" :key="index" class="entity-item">
										<div class="entity-type">{{ entity.entityType }}</div>
										<div class="entity-text">{{ entity.text }}</div>
										<div class="entity-score">Score: {{ (entity.score * 100).toFixed(1) }}%</div>
									</div>
								</div>
							</div>
							<div v-else class="empty-state">
								<p>No entities detected in this document.</p>
							</div>
						</BTab>

						<!-- WCAG Compliance Tab -->
						<BTab title="WCAG Compliance">
							<div v-if="reportStore.reportItem.wcagComplianceResults && Object.keys(reportStore.reportItem.wcagComplianceResults).length > 0">
								<div class="detail-grid">
									<div v-for="(value, key) in reportStore.reportItem.wcagComplianceResults" :key="key" class="detail-item">
										<span class="detail-label">{{ formatKey(key) }}:</span>
										<span class="detail-value">{{ formatValue(value) }}</span>
									</div>
								</div>
							</div>
							<div v-else class="empty-state">
								<p>No WCAG compliance results available.</p>
							</div>
						</BTab>

						<!-- Language Level Tab -->
						<BTab title="Language Level">
							<div v-if="reportStore.reportItem.languageLevelResults && Object.keys(reportStore.reportItem.languageLevelResults).length > 0">
								<div class="detail-grid">
									<div v-for="(value, key) in reportStore.reportItem.languageLevelResults" :key="key" class="detail-item">
										<span class="detail-label">{{ formatKey(key) }}:</span>
										<span class="detail-value">{{ formatValue(value) }}</span>
									</div>
								</div>
							</div>
							<div v-else class="empty-state">
								<p>No language level results available.</p>
							</div>
						</BTab>

						<!-- Retention Tab -->
						<BTab title="Retention">
							<div class="detail-grid">
								<div class="detail-item">
									<span class="detail-label">Retention Period:</span>
									<span class="detail-value">{{ reportStore.reportItem.retentionPeriod || 'Indefinite' }} {{ reportStore.reportItem.retentionPeriod ? 'days' : '' }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">Retention Expiry:</span>
									<span class="detail-value">{{ reportStore.reportItem.retentionExpiry || 'Not set' }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">Legal Basis:</span>
									<span class="detail-value">{{ reportStore.reportItem.legalBasis || 'Not specified' }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">Data Controller:</span>
									<span class="detail-value">{{ reportStore.reportItem.dataController || 'Not specified' }}</span>
								</div>
							</div>
						</BTab>
					</BTabs>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * Component for displaying and managing report details
 * Includes functionality for viewing report data, downloading reports,
 * and managing report lifecycle
 * 
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcListItem, NcNoteCard, NcCounterBubble, NcBadge } from '@nextcloud/vue'


// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Download from 'vue-material-design-icons/Download.vue'

export default {
	name: 'FileDetails',
	components: {
		// Components
		NcActions,
		NcActionButton,
		NcListItem,
		NcNoteCard,
		NcCounterBubble,
		NcBadge,
		BTabs,
		BTab,
		// Icons
		DotsHorizontal,
		Pencil,
		FileOutline,
		Delete,
		Download,
	},
	methods: {
		/**
		 * Download the report
		 */
		downloadReport() {
			const reportId = reportStore.reportItem.id
			fetch(`reports/${reportId}/download`)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok')
					}
					return response.blob()
				})
				.then(blob => {
					const link = document.createElement('a')
					link.href = window.URL.createObjectURL(blob)
					link.download = `${reportStore.reportItem.fileName}`
					link.click()
					window.URL.revokeObjectURL(link.href)
				})
				.catch(error => {
					console.error('Error downloading report:', error)
				})
		},
		
		/**
		 * Format file size to human-readable format
		 * 
		 * @param {number} bytes - File size in bytes
		 * @returns {string} Formatted file size
		 */
		formatFileSize(bytes) {
			if (bytes === 0) return '0 Bytes'
			
			const k = 1024
			const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
			const i = Math.floor(Math.log(bytes) / Math.log(k))
			
			return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
		},
		
		/**
		 * Format object key to display format
		 * 
		 * @param {string} key - Object key
		 * @returns {string} Formatted key
		 */
		formatKey(key) {
			return key
				.replace(/_/g, ' ')
				.replace(/\b\w/g, l => l.toUpperCase())
		},
		
		/**
		 * Format value for display
		 * 
		 * @param {any} value - Value to format
		 * @returns {string} Formatted value
		 */
		formatValue(value) {
			if (value === null || value === undefined) {
				return 'Not available'
			}
			
			if (typeof value === 'boolean') {
				return value ? 'Yes' : 'No'
			}
			
			if (Array.isArray(value)) {
				return value.length > 0 ? value.join(', ') : 'None'
			}
			
			if (typeof value === 'object') {
				return JSON.stringify(value, null, 2)
			}
			
			return value.toString()
		},
		
		/**
		 * Get badge type based on report status
		 * 
		 * @param {string} status - Report status
		 * @returns {string} Badge type
		 */
		getStatusBadgeType(status) {
			switch (status) {
				case 'completed':
					return 'success'
				case 'processing':
					return 'primary'
				case 'pending':
					return 'warning'
				case 'failed':
					return 'error'
				default:
					return 'secondary'
			}
		},
		
		/**
		 * Get badge type based on risk level
		 * 
		 * @param {string} riskLevel - Risk level
		 * @returns {string} Badge type
		 */
		getRiskLevelBadgeType(riskLevel) {
			switch (riskLevel.toLowerCase()) {
				case 'low':
					return 'success'
				case 'medium':
					return 'warning'
				case 'high':
					return 'error'
				case 'critical':
					return 'error'
				default:
					return 'secondary'
			}
		},
		
		/**
		 * Get CSS class for risk level
		 * 
		 * @param {string} riskLevel - Risk level
		 * @returns {string} CSS class
		 */
		getRiskLevelClass(riskLevel) {
			switch (riskLevel?.toLowerCase()) {
				case 'low':
					return 'risk-low'
				case 'medium':
					return 'risk-medium'
				case 'high':
					return 'risk-high'
				case 'critical':
					return 'risk-critical'
				default:
					return 'risk-unknown'
			}
		},
		
		/**
		 * Format risk score for display
		 * 
		 * @param {number|null} score - Risk score
		 * @returns {string} Formatted risk score
		 */
		formatRiskScore(score) {
			if (score === null || score === undefined) {
				return 'N/A';
			}
			return Math.round(score).toString();
		},
		
		/**
		 * Get CSS class for risk score
		 * 
		 * @param {number|null} score - Risk score
		 * @returns {string} CSS class
		 */
		getRiskScoreClass(score) {
			if (score === null || score === undefined) {
				return 'risk-unknown';
			}
			
			if (score < 20) {
				return 'risk-low';
			} else if (score < 50) {
				return 'risk-medium';
			} else if (score < 80) {
				return 'risk-high';
			} else {
				return 'risk-critical';
			}
		},
		
		/**
		 * Get explanation for risk level
		 * 
		 * @param {string} riskLevel - Risk level
		 * @returns {string} Risk explanation
		 */
		getRiskExplanation(riskLevel) {
			switch (riskLevel?.toLowerCase()) {
				case 'low':
					return 'This document contains minimal sensitive information and poses little privacy risk.';
				case 'medium':
					return 'This document contains some sensitive information that may require attention.';
				case 'high':
					return 'This document contains significant sensitive information and should be handled with care.';
				case 'critical':
					return 'This document contains highly sensitive information and requires immediate attention.';
				default:
					return 'Risk level could not be determined for this document.';
			}
		},
		
		/**
		 * Get unique entity types and their counts
		 * 
		 * @param {Array} entities - List of entities
		 * @returns {Array} Entity types with counts
		 */
		getEntityTypes(entities) {
			if (!entities || !Array.isArray(entities)) {
				return [];
			}
			
			const typeCounts = {};
			
			entities.forEach(entity => {
				const type = entity.entityType || 'UNKNOWN';
				typeCounts[type] = (typeCounts[type] || 0) + 1;
			});
			
			return Object.keys(typeCounts).map(type => ({
				type,
				count: typeCounts[type]
			})).sort((a, b) => this.getEntityWeight(b.type) - this.getEntityWeight(a.type));
		},
		
		/**
		 * Format entity type for display
		 * 
		 * @param {string} entityType - Entity type
		 * @returns {string} Formatted entity type
		 */
		formatEntityType(entityType) {
			return entityType
				.replace(/_/g, ' ')
				.replace(/\b\w/g, l => l.toUpperCase());
		},
		
		/**
		 * Get weight for entity type
		 * 
		 * @param {string} entityType - Entity type
		 * @returns {number} Entity weight
		 */
		getEntityWeight(entityType) {
			const weights = {
				'PERSON': 5.0,
				'EMAIL_ADDRESS': 8.0,
				'PHONE_NUMBER': 7.0,
				'CREDIT_CARD': 10.0,
				'IBAN_CODE': 9.0,
				'US_SSN': 10.0,
				'US_BANK_NUMBER': 9.0,
				'LOCATION': 3.0,
				'DATE_TIME': 1.0,
				'NRP': 8.0,
				'IP_ADDRESS': 6.0,
				'US_DRIVER_LICENSE': 8.0,
				'US_PASSPORT': 9.0,
				'US_ITIN': 9.0,
				'MEDICAL_LICENSE': 7.0,
				'URL': 2.0
			};
			
			return weights[entityType] || 4.0; // Default weight
		},
	},
}
</script>

<style>
h4 {
  font-weight: bold;
}

.head{
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 16px;
}

.button{
	max-height: 10px;
}

.h1 {
  display: block !important;
  font-size: 2em !important;
  margin-block-start: 0.67em !important;
  margin-block-end: 0.67em !important;
  margin-inline-start: 0px !important;
  margin-inline-end: 0px !important;
  font-weight: bold !important;
  unicode-bidi: isolate !important;
}

.dataContent {
  display: flex;
  flex-direction: column;
}

.section-container {
  margin: 20px 0;
  padding: 16px;
  background-color: var(--color-main-background-translucent);
  border-radius: 8px;
  border: 1px solid var(--color-border);
}

.section-container h2 {
  margin-top: 0;
  margin-bottom: 16px;
  font-size: 1.2em;
  font-weight: bold;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 12px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  padding: 8px;
  background-color: var(--color-background-hover);
  border-radius: 4px;
}

.detail-label {
  font-weight: bold;
  color: var(--color-text-maxcontrast);
  margin-bottom: 4px;
}

.detail-value {
  word-break: break-word;
}

.status-badge-container {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.status-badge, .risk-badge {
  font-size: 0.9em;
}

.entity-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 12px;
}

.entity-item {
  padding: 12px;
  background-color: var(--color-background-hover);
  border-radius: 4px;
  border-left: 4px solid var(--color-primary);
}

.entity-type {
  font-weight: bold;
  margin-bottom: 4px;
}

.entity-text {
  margin-bottom: 4px;
  word-break: break-word;
}

.entity-score {
  font-size: 0.9em;
  color: var(--color-text-maxcontrast);
}

.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 32px;
  color: var(--color-text-maxcontrast);
  font-style: italic;
}

.tabContainer {
  margin-top: 20px;
}

/* Risk Assessment Styles */
.risk-score-container {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 20px;
}

.risk-score-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2em;
  font-weight: bold;
  color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.risk-score-value {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.risk-score-details {
  flex: 1;
}

.risk-score-details h3 {
  margin-top: 0;
  margin-bottom: 8px;
}

.risk-level-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 4px;
  font-weight: bold;
  color: white;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  text-transform: capitalize;
}

.risk-explanation {
  margin: 0;
  color: var(--color-text-maxcontrast);
}

.risk-factors {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--color-border);
}

.risk-factors h3 {
  margin-top: 0;
  margin-bottom: 8px;
}

.risk-factors-list {
  list-style-type: none;
  padding: 0;
  margin: 12px 0;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 8px;
}

.risk-factors-list li {
  padding: 8px 12px;
  background-color: var(--color-background-hover);
  border-radius: 4px;
  border-left: 3px solid var(--color-primary);
}

.entity-weight {
  font-size: 0.85em;
  color: var(--color-text-maxcontrast);
  margin-left: 4px;
}

.risk-calculation-note {
  font-size: 0.9em;
  font-style: italic;
  color: var(--color-text-maxcontrast);
  margin-top: 12px;
}

/* Risk color classes */
.risk-low {
  background-color: #2ecc71;
  border-color: #27ae60;
}

.risk-medium {
  background-color: #f39c12;
  border-color: #e67e22;
}

.risk-high {
  background-color: #e74c3c;
  border-color: #c0392b;
}

.risk-critical {
  background-color: #c0392b;
  border-color: #922b21;
}

.risk-unknown {
  background-color: #95a5a6;
  border-color: #7f8c8d;
}
</style>
