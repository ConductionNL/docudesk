<script setup>
import { reportStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="head">
					<h1 class="h1">
						{{ reportStore.reportItem.file_name }}
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
						<NcActionButton @click="downloadReport()">
							<template #icon>
								<Download :size="20" />
							</template>
							Download Report
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteReport')">
							<template #icon>
								<Delete :size="20" />
							</template>
							Delete
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
						v-if="reportStore.reportItem.risk_level" 
						:type="getRiskLevelBadgeType(reportStore.reportItem.risk_level)" 
						class="risk-badge">
						Risk: {{ reportStore.reportItem.risk_level }}
					</NcBadge>
				</div>

				<!-- Error Message -->
				<NcNoteCard v-if="reportStore.reportItem.error_message" type="error">
					{{ reportStore.reportItem.error_message }}
				</NcNoteCard>

				<!-- File Information Section -->
				<div class="section-container">
					<h2>File Information</h2>
					<div class="detail-grid">
						<div class="detail-item">
							<span class="detail-label">File Path:</span>
							<span class="detail-value">{{ reportStore.reportItem.file_path }}</span>
						</div>
						<div class="detail-item">
							<span class="detail-label">File Type:</span>
							<span class="detail-value">{{ reportStore.reportItem.file_type }}</span>
						</div>
						<div class="detail-item">
							<span class="detail-label">File Extension:</span>
							<span class="detail-value">{{ reportStore.reportItem.file_extension }}</span>
						</div>
						<div class="detail-item">
							<span class="detail-label">File Size:</span>
							<span class="detail-value">{{ formatFileSize(reportStore.reportItem.file_size) }}</span>
						</div>
						<div class="detail-item">
							<span class="detail-label">File Hash:</span>
							<span class="detail-value">{{ reportStore.reportItem.file_hash }}</span>
						</div>
						<div class="detail-item">
							<span class="detail-label">Node ID:</span>
							<span class="detail-value">{{ reportStore.reportItem.node_id }}</span>
						</div>
					</div>
				</div>

				<!-- Tabs for different analysis results -->
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<!-- Entities Tab -->
						<BTab title="Entities">
							<div v-if="reportStore.reportItem.entities && reportStore.reportItem.entities.length > 0">
								<div class="entity-list">
									<div v-for="(entity, index) in reportStore.reportItem.entities" :key="index" class="entity-item">
										<div class="entity-type">{{ entity.entity_type }}</div>
										<div class="entity-text">{{ entity.text }}</div>
										<div class="entity-score">Score: {{ (entity.score * 100).toFixed(1) }}%</div>
									</div>
								</div>
							</div>
							<div v-else class="empty-state">
								<p>No entities detected in this document.</p>
							</div>
						</BTab>

						<!-- Anonymization Results Tab -->
						<BTab title="Anonymization">
							<div v-if="reportStore.reportItem.anonymization_results && Object.keys(reportStore.reportItem.anonymization_results).length > 0">
								<div class="detail-grid">
									<div v-for="(value, key) in reportStore.reportItem.anonymization_results" :key="key" class="detail-item">
										<span class="detail-label">{{ formatKey(key) }}:</span>
										<span class="detail-value">{{ formatValue(value) }}</span>
									</div>
								</div>
							</div>
							<div v-else class="empty-state">
								<p>No anonymization results available.</p>
							</div>
						</BTab>

						<!-- WCAG Compliance Tab -->
						<BTab title="WCAG Compliance">
							<div v-if="reportStore.reportItem.wcag_compliance_results && Object.keys(reportStore.reportItem.wcag_compliance_results).length > 0">
								<div class="detail-grid">
									<div v-for="(value, key) in reportStore.reportItem.wcag_compliance_results" :key="key" class="detail-item">
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
							<div v-if="reportStore.reportItem.language_level_results && Object.keys(reportStore.reportItem.language_level_results).length > 0">
								<div class="detail-grid">
									<div v-for="(value, key) in reportStore.reportItem.language_level_results" :key="key" class="detail-item">
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
									<span class="detail-value">{{ reportStore.reportItem.retention_period || 'Indefinite' }} {{ reportStore.reportItem.retention_period ? 'days' : '' }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">Retention Expiry:</span>
									<span class="detail-value">{{ reportStore.reportItem.retention_expiry || 'Not set' }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">Legal Basis:</span>
									<span class="detail-value">{{ reportStore.reportItem.legal_basis || 'Not specified' }}</span>
								</div>
								<div class="detail-item">
									<span class="detail-label">Data Controller:</span>
									<span class="detail-value">{{ reportStore.reportItem.data_controller || 'Not specified' }}</span>
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
					link.download = `${reportStore.reportItem.file_name}`
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
		}
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
</style>
