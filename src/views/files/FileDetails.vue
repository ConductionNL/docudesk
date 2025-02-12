<script setup>
import { fileStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="head">
					<h1 class="h1">
						{{ fileStore.fileItem.name }}
					</h1>
					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editFile')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit File
						</NcActionButton>
						<NcActionButton @click="downloadFile()">
							<template #icon>
								<Download :size="20" />
							</template>
							Download File
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteFile')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
				<NcNoteCard v-if="fileStore.fileItem.notice" type="info">
					{{ fileStore.fileItem.notice }}
				</NcNoteCard>
				<div class="detailGrid">
					<div>
						<b>Summary:</b>
						<span>{{ fileStore.fileItem.summary }}</span>
					</div>
				</div>
				<span>{{ fileStore.fileItem.description }}</span>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab active>
							<template #title>
								NcAppNavigationSettings
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
 * Component for displaying and managing file details
 * Includes functionality for editing files, managing file rules,
 * and downloading files
 */
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcListItem, NcNoteCard, NcCounterBubble } from '@nextcloud/vue'


// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import FileDocumentPlusOutline from 'vue-material-design-icons/FileDocumentPlusOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Download from 'vue-material-design-icons/Download.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'

export default {
	name: 'FileDetails',
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
		FileOutline,
	},
	methods: {
		downloadFile() {
			const fileId = fileStore.fileItem.id
			fetch(`files/${fileId}/download`)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok')
					}
					return response.blob()
				})
				.then(blob => {
					const link = document.createElement('a')
					link.href = window.URL.createObjectURL(blob)
					link.download = `${fileStore.fileItem.name}`
					link.click()
					window.URL.revokeObjectURL(link.href)
				})
				.catch(error => {
					console.error('Error downloading file:', error)
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
