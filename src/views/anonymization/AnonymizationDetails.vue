<script setup>
import { anonymizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="head">
					<h1 class="h1">
						{{ anonymizationStore.anonymizationItem.name }}
					</h1>
					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editAnonymization')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit Document
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addAnonymizationRule')">
							<template #icon>
								<FileDocumentPlusOutline :size="20" />
							</template>
							Add Anonymization Rules
						</NcActionButton>
						<NcActionButton @click="downloadAnonymizedPdf()">
							<template #icon>
								<Download :size="20" />
							</template>
							Download Anonymized PDF
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteAnonymization')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
				<NcNoteCard v-if="anonymizationStore.anonymizationItem.notice" type="info">
					{{ anonymizationStore.anonymizationItem.notice }}
				</NcNoteCard>
				<div class="detailGrid">
					<div>
						<b>Summary:</b>
						<span>{{ anonymizationStore.anonymizationItem.summary }}</span>
					</div>
				</div>
				<span>{{ anonymizationStore.anonymizationItem.description }}</span>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab active>
							<template #title>
								searchField
							</template>							
						</BTab>
					</BTabs>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * Component for displaying and managing document anonymization details
 * Includes functionality for editing documents, managing anonymization rules,
 * and downloading anonymized PDFs
 */
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcListItem, NcNoteCard, NcCounterBubble } from '@nextcloud/vue'


// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import FileDocumentPlusOutline from 'vue-material-design-icons/FileDocumentPlusOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Download from 'vue-material-design-icons/Download.vue'
import Incognito from 'vue-material-design-icons/Incognito.vue'

export default {
	name: 'AnonymizationDetails',
	components: {
		// Components
		NcActions,
		NcActionButton,
		NcListItem,
		NcNoteCard,
		NcCounterBubble,
		BTabs,
		BTab,
		// Icons
		DotsHorizontal,
		Pencil,
		FileDocumentPlusOutline,
		TrashCanOutline,
		Download,
		Incognito,
	},
	methods: {
		downloadAnonymizedPdf() {
			const documentId = anonymizationStore.anonymizationItem.id
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
					link.download = `${anonymizationStore.anonymizationItem.name}_anonymized.pdf`
					link.click()
					window.URL.revokeObjectURL(link.href)
				})
				.catch(error => {
					console.error('Error downloading PDF:', error)
				})
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

/* Add margin to counter bubble only when inside nav-item */
.nav-item .counter-bubble__counter {
    margin-left: 10px;
}
</style>
