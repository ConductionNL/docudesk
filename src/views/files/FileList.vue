<script setup>
import { fileStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="fileStore.searchTerm"
					:show-trailing-button="fileStore.searchTerm !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@input="fileStore.setSearchTerm($event.target.value)"
					@trailing-button-click="fileStore.clearSearch()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="fileStore.refreshFileList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="fileStore.setFileItem(null); navigationStore.setModal('editFile')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add File
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="fileStore.fileList && fileStore.fileList.length > 0 && !fileStore.isLoadingFileList">
				<NcListItem v-for="(file, i) in fileStore.fileList"
					:key="`${file}${i}`"
					:name="file?.name"
					:force-display-actions="true"
					:active="fileStore.fileItem?.id === file?.id"
					:counter-number="file?.rules?.length || 0"
					@click="handleFileSelect(file)">
					<template #icon>
						<File :class="fileStore.fileItem?.id === file?.id && 'selectedIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ file?.summary || 'No summary available' }}
					</template>
					<template #actions>
						<NcActionButton @click="fileStore.setFileItem(file); navigationStore.setModal('editFile')">
							<template #icon>
								<Pencil />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="fileStore.setFileItem(file); navigationStore.setDialog('deleteFile')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Delete
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="fileStore.isLoadingFileList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading files" />

		<div v-if="fileStore.fileList.length === 0 && !fileStore.isLoadingFileList">
			No files have been added yet.
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of files
 */
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import File from 'vue-material-design-icons/File.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

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
		// Icons
		File,
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	mounted() {
		fileStore.refreshFileList()
	},
	methods: {
		/**
		 * Handle file selection
		 * @param {object} file - The selected file object
		 */
		async handleFileSelect(file) {
			// Set the selected file in the store
			fileStore.setFileItem(file)
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
}

.searchField {
    padding-inline-start: 65px;
    padding-inline-end: 20px;
    margin-block-end: 6px;
}

.selectedIcon>svg {
    fill: white;
}

.loadingIcon {
    margin-block-start: var(--OC-margin-20);
}
</style>
