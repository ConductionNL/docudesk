<script setup>
import { fileStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<FileList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!fileStore.fileItem || navigationStore.selected != 'files'"
				class="detailContainer" 
				name="No Files"
				description="No files selected">
				<template #icon>
					<File />
				</template>
				<template #action>
					<NcButton type="primary" @click="fileStore.setFileItem(null); navigationStore.setModal('editFile')">
						Add File
					</NcButton>
				</template>
			</NcEmptyContent>
			<FileDetails v-if="fileStore.fileItem && navigationStore.selected === 'files'" />
		</template>
	</NcAppContent>
</template>

<script>
/**
 * Main component for the files view that handles displaying the list of files
 * and their details
 */
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import FileList from './FileList.vue'
import FileDetails from './FileDetails.vue'
import File from 'vue-material-design-icons/File.vue'

export default {
	name: 'FileIndex',
	components: {
		NcAppContent,
		NcEmptyContent, 
		NcButton,
		FileList,
		FileDetails,
		File,
	},
}
</script>
