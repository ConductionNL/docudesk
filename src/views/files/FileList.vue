<script setup>
import { reportStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcActions>
					<NcActionButton @click="reportStore.refreshReportList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="reportStore.setReportItem(null); navigationStore.setModal('editReport')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add Report
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="reportStore.reportList && reportStore.reportList.length > 0 && !reportStore.isLoadingReportList">
				<NcListItem v-for="(report, i) in reportStore.reportList"
					:key="`${report.id}${i}`"
					:name="report.fileName || 'Unnamed file'"
					:force-display-actions="true"
					:active="reportStore.reportItem?.id === report?.id"
					@click="handleReportSelect(report)">
					<template #icon>
						<div class="file-icon-container">
							<FileOutline :size="44" />
							<NcBadge v-if="report.status" 
								:type="getStatusBadgeType(report.status)" 
								class="status-badge">
								{{ report.status }}
							</NcBadge>
						</div>
					</template>
					<template #subname>
						<div class="report-subname">
							<span class="file-path">{{ formatFilePath(report.filePath) }}</span>
							<span v-if="report.riskLevel" 
								:class="['risk-level', getRiskLevelClass(report.riskLevel)]">
								Risk: {{ report.riskLevel }}
							</span>
						</div>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="reportStore.isLoadingReportList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading reports" />

		<div v-if="reportStore.reportList.length === 0 && !reportStore.isLoadingReportList" class="empty-state">
			<p>No reports have been added yet.</p>
			<NcButton type="primary" @click="reportStore.setReportItem(null); navigationStore.setModal('editReport')">
				Add Report
			</NcButton>
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of reports
 * 
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcButton, NcBadge } from '@nextcloud/vue'
import FileIcon from '../../components/FileIcon.vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'

export default {
	name: 'FileList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		NcButton,
		NcBadge,
		FileIcon,
		// Icons
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
		Eye,
		FileOutline,
	},
	mounted() {
		reportStore.refreshReportList()
	},
	methods: {
		/**
		 * Handle report selection
		 * @param {object} report - The selected report object
		 */
		async handleReportSelect(report) {
			// Set the selected report in the store
			reportStore.setReportItem(report)
			// Ensure we're in the files view
			navigationStore.setSelected('files')
		},
		
		/**
		 * Format file path for display
		 * @param {string} path - The file path
		 * @returns {string} Formatted file path
		 */
		formatFilePath(path) {
			if (!path) return '';
			
			// If path is too long, truncate it
			if (path.length > 40) {
				const parts = path.split('/');
				const fileName = parts.pop();
				const directory = parts.join('/');
				return directory.substring(0, 20) + '.../' + fileName;
			}
			
			return path;
		},
		
		/**
		 * Get badge type based on report status
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
		 * Get CSS class for risk level
		 * @param {string} riskLevel - Risk level
		 * @returns {string} CSS class
		 */
		getRiskLevelClass(riskLevel) {
			switch (riskLevel.toLowerCase()) {
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
	},
}
</script>

<style>
.listHeader {
    position: sticky;
    top: 0;
    z-index: 1000;
    background-color: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
    padding-bottom: 8px;
}

.searchField {
    padding-inline-start: 65px;
    padding-inline-end: 20px;
    margin-block-end: 6px;
}

.loadingIcon {
    margin-block-start: var(--OC-margin-20);
}

.file-icon-container {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-badge {
    position: absolute;
    bottom: -5px;
    right: -5px;
    font-size: 0.7em;
    padding: 2px 6px;
}

.report-subname {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.file-path {
    color: var(--color-text-maxcontrast);
    font-size: 0.9em;
}

.risk-level {
    font-size: 0.85em;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

.risk-low {
    color: #2ecc71;
    background-color: rgba(46, 204, 113, 0.1);
}

.risk-medium {
    color: #f39c12;
    background-color: rgba(243, 156, 18, 0.1);
}

.risk-high {
    color: #e74c3c;
    background-color: rgba(231, 76, 60, 0.1);
}

.risk-critical {
    color: #c0392b;
    background-color: rgba(192, 57, 43, 0.1);
}

.risk-unknown {
    color: var(--color-text-maxcontrast);
    background-color: rgba(127, 140, 141, 0.1);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    gap: 16px;
}
</style>
