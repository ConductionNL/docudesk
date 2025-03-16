<script setup>
import { reportStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<FileList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!reportStore.reportItem || navigationStore.selected != 'files'"
				class="detailContainer" 
				name="No Reports"
				description="No reports selected">
				<template #icon>
					<File />
				</template>
				<template #action>
					<NcButton type="primary" @click="reportStore.setReportItem(null); navigationStore.setModal('editReport')">
						Add Report
					</NcButton>
				</template>
			</NcEmptyContent>
			<FileDetails v-if="reportStore.reportItem && navigationStore.selected === 'files'" />
		</template>
	</NcAppContent>
</template>

<script>
/**
 * Main component for the reports view that handles displaying the list of reports
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
