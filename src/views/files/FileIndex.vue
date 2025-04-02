<script setup>
import { reportStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<FileList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!reportStore.reportItem || navigationStore.selected !== 'files'"
				class="detailContainer" 
				name="No Report Selected"
				description="Select a report from the list or create a new one">
				<template #icon>
					<FileOutline :size="64" />
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
 * 
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import FileList from './FileList.vue'
import FileDetails from './FileDetails.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'

export default {
	name: 'FileIndex',
	components: {
		NcAppContent,
		NcEmptyContent, 
		NcButton,
		FileList,
		FileDetails,
		FileOutline,
	},
}
</script>

<style>
.detailContainer {
	padding: 20px;
	height: 100%;
	overflow-y: auto;
}
</style>
