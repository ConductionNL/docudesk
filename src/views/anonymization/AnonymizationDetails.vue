<script setup>
import { objectStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div class="head">
			<h1 class="h1">
				{{ document.name }}
			</h1>

			<NcActions :disabled="objectStore.isLoading('anonymization')"
				:primary="true"
				:inline="1"
				:menu-name="objectStore.isLoading('anonymization') ? 'Loading...' : 'Actions'">
				<template #icon>
					<span>
						<NcLoadingIcon v-if="objectStore.isLoading('anonymization')"
							:size="20"
							appearance="dark" />
						<DotsHorizontal v-if="!objectStore.isLoading('anonymization')" :size="20" />
					</span>
				</template>
				<NcActionButton @click="navigationStore.setModal('editAnonymization')">
					<template #icon>
						<Pencil :size="20" />
					</template>
					Edit Document
				</NcActionButton>
				<NcActionButton @click="navigationStore.setModal('addAnonymizationRule')">
					<template #icon>
						<FileOutline :size="20" />
					</template>
					Add Anonymization Rules
				</NcActionButton>
				<NcActionButton @click="downloadAnonymizedPdf()">
					<template #icon>
						<Download :size="20" />
					</template>
					Download Anonymized PDF
				</NcActionButton>
				<NcActionButton @click="navigationStore.setDialog('deleteObject', { objectType: 'anonymization', dialogTitle: 'Document' })">
					<template #icon>
						<TrashCanOutline :size="20" />
					</template>
					Delete
				</NcActionButton>
			</NcActions>
		</div>

		<div class="container">
			<NcNoteCard v-if="document.notice" type="info">
				{{ document.notice }}
			</NcNoteCard>

			<div class="detailGrid">
				<div>
					<b>Summary:</b>
					<span>{{ document.summary }}</span>
				</div>
				<div>
					<b>Description:</b>
					<span>{{ document.description || '-' }}</span>
				</div>
			</div>

			<div class="tabContainer">
				<BTabs content-class="mt-3" justified>
					<BTab title="Anonymization Rules" active>
						<div v-if="document.rules && document.rules.length > 0">
							<NcListItem v-for="(rule, i) in document.rules"
								:key="`${rule}${i}`"
								:name="rule.name || 'Unnamed Rule'"
								:force-display-actions="true">
								<template #icon>
									<FileOutline :size="44" />
								</template>
								<template #subname>
									{{ rule.description || 'No description available' }}
								</template>
								<template #actions>
									<NcActionButton @click="navigationStore.setModal('editAnonymizationRule', rule)">
										<template #icon>
											<Pencil :size="20" />
										</template>
										Edit Rule
									</NcActionButton>
									<NcActionButton @click="navigationStore.setDialog('deleteAnonymizationRule', rule)">
										<template #icon>
											<TrashCanOutline :size="20" />
										</template>
										Delete Rule
									</NcActionButton>
								</template>
							</NcListItem>
						</div>
						<div v-else class="empty-state">
							<p>No anonymization rules defined for this document.</p>
							<NcButton type="primary" @click="navigationStore.setModal('addAnonymizationRule')">
								Add Rule
							</NcButton>
						</div>
					</BTab>

					<BTab title="Processing History">
						<div v-if="document.processingHistory && document.processingHistory.length > 0">
							<NcListItem v-for="(history, i) in document.processingHistory"
								:key="`${history}${i}`"
								:name="history.timestamp"
								:force-display-actions="true">
								<template #icon>
									<ClockOutline :size="44" />
								</template>
								<template #subname>
									{{ history.description || 'No description available' }}
								</template>
								<template #actions>
									<NcActionButton @click="downloadHistoryFile(history)">
										<template #icon>
											<Download :size="20" />
										</template>
										Download File
									</NcActionButton>
								</template>
							</NcListItem>
						</div>
						<div v-else class="empty-state">
							<p>No processing history available for this document.</p>
						</div>
					</BTab>
				</BTabs>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * Component for displaying and managing document anonymization details
 * Includes functionality for editing documents, managing anonymization rules,
 * and downloading anonymized PDFs
 * 
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcListItem, NcNoteCard, NcButton, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Download from 'vue-material-design-icons/Download.vue'
import ClockOutline from 'vue-material-design-icons/ClockOutline.vue'

export default {
	name: 'AnonymizationDetails',
	components: {
		// Components
		NcActions,
		NcActionButton,
		NcListItem,
		NcNoteCard,
		NcButton,
		NcLoadingIcon,
		BTabs,
		BTab,
		// Icons
		DotsHorizontal,
		Pencil,
		FileOutline,
		TrashCanOutline,
		Download,
		ClockOutline,
	},
	computed: {
		document() {
			return objectStore.getActiveObject('anonymization')
		},
	},
	methods: {
		/**
		 * Download the anonymized PDF
		 */
		downloadAnonymizedPdf() {
			const documentId = this.document.id
			fetch(`anonymization/${documentId}/download`)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok')
					}
					return response.blob()
				})
				.then(blob => {
					const link = document.createElement('a')
					link.href = window.URL.createObjectURL(blob)
					link.download = `${this.document.name}_anonymized.pdf`
					link.click()
					window.URL.revokeObjectURL(link.href)
				})
				.catch(error => {
					console.error('Error downloading PDF:', error)
				})
		},

		/**
		 * Download a file from processing history
		 * 
		 * @param {Object} history - The history entry containing file information
		 */
		downloadHistoryFile(history) {
			const documentId = this.document.id
			fetch(`anonymization/${documentId}/history/${history.id}/download`)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok')
					}
					return response.blob()
				})
				.then(blob => {
					const link = document.createElement('a')
					link.href = window.URL.createObjectURL(blob)
					link.download = `${this.document.name}_${history.timestamp}.pdf`
					link.click()
					window.URL.revokeObjectURL(link.href)
				})
				.catch(error => {
					console.error('Error downloading history file:', error)
				})
		},
	},
}
</script>

<style>
h4 {
  font-weight: bold;
}

.head {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 16px;
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

.container {
	padding: 20px;
}

.detailGrid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 16px;
	margin: 20px 0;
}

.detailGrid > div {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.detailGrid b {
	color: var(--color-text-maxcontrast);
}

.tabContainer {
	margin-top: 20px;
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
