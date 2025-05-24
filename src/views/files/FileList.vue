<script setup>
import { objectStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="objectStore.getSearchTerm('report')"
					:show-trailing-button="objectStore.getSearchTerm('report') !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@update:value="(value) => objectStore.setSearchTerm('report', value)"
					@trailing-button-click="objectStore.clearSearch('report')">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="objectStore.fetchCollection('report')">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="objectStore.clearActiveObject('report'); navigationStore.setModal('editReport')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add Report
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="!objectStore.isLoading('report')">
				<NcListItem v-for="(report, i) in objectStore.getCollection('report').results"
					:key="`${report.id}${i}`"
					:name="report.fileName || 'Unnamed file'"
					:force-display-actions="true"
					:active="objectStore.getActiveObject('report')?.id === report?.id"
					@click="handleReportSelect(report)">
					<template #icon>
						<div class="file-icon-container">
							<FileOutline :size="44" />
							<NcCounterBubble v-if="report.status"
								:type="report.status === 'active' ? 'success' : 'error'"
								:class="{ 'status-badge': true }">
								{{ report.status }}
							</NcCounterBubble>
						</div>
					</template>
					<template #subname>
						<div class="report-subname">
							<span v-if="report.riskLevel"
								class="risk-level-badge"
								:class="getRiskLevelClass(report.riskLevel)">
								{{ report.riskLevel }}
							</span>
						</div>
					</template>
					<template #actions>
						<NcActionButton @click="objectStore.setActiveObject('report', report); navigationStore.setModal('editReport')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="objectStore.setActiveObject('report', report); navigationStore.setDialog('deleteObject', { objectType: 'report', dialogTitle: 'Report' })">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="objectStore.isLoading('report')"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading reports" />

		<div v-if="!objectStore.getCollection('report').results.length" class="empty-state">
			<p>No reports have been added yet.</p>
			<NcButton type="primary" @click="objectStore.clearActiveObject('report'); navigationStore.setModal('editReport')">
				Add Report
			</NcButton>
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of reports
 *
 * @package
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcButton, NcCounterBubble } from '@nextcloud/vue'
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
		NcCounterBubble,
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
		objectStore.fetchCollection('report')
	},
	methods: {
		/**
		 * Handle report selection
		 * @param {object} report - The selected report object
		 */
		async handleReportSelect(report) {
			// Set the selected report in the store
			objectStore.setActiveObject('report', report)
			// Ensure we're in the files view
			navigationStore.setSelected('files')
		},

		/**
		 * Format file path for display
		 * @param {string} path - The file path
		 * @return {string} Formatted file path
		 */
		formatFilePath(path) {
			if (!path) return ''

			// If path is too long, truncate it
			if (path.length > 40) {
				const parts = path.split('/')
				const fileName = parts.pop()
				const directory = parts.join('/')
				return directory.substring(0, 20) + '.../' + fileName
			}

			return path
		},

		/**
		 * Get badge type based on report status
		 * @param {string} status - Report status
		 * @return {string} Badge type
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
		 * @return {string} CSS class
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
    display: flex;
    flex-direction: row;
    align-items: center;
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

.risk-level-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: bold;
    color: white;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    text-transform: capitalize;
    font-size: 0.75em;
}

.risk-badge {
    font-size: 0.75em;
    font-weight: normal;
    padding: 1px 6px;
    border-radius: 10px;
    display: inline-block;
    width: fit-content;
    text-transform: capitalize;
}

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
