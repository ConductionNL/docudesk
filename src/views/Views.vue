<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Main content container for all views -->
	<NcAppContent>
		<template #default>
			<FileIndex v-if="navigationStore.selected === 'files'" />
			<Dashboard v-if="navigationStore.selected === 'dashboard'" />
			<AnonymizationList v-if="navigationStore.selected === 'anonymization'" />
			<SigningIndex v-if="navigationStore.selected === 'signing'" />
			<WcagIndex v-if="navigationStore.selected === 'wcag'" />
			<TemplatesIndex v-if="navigationStore.selected === 'templates'" />
		</template>
	</NcAppContent>
</template>

<script>
/**
 * Main view component that handles routing between different views based on navigation selection
 * Uses the navigationStore to determine which view to display
 */
import { NcAppContent } from '@nextcloud/vue'

// View components
import FileIndex from './files/FileIndex.vue'
import Dashboard from './dashboard/DashboardIndex.vue'
import AnonymizationList from './anonymization/AnonymizationList.vue'
import SigningIndex from './signing/SigningIndex.vue'
import WcagIndex from './wcag/WcagIndex.vue'
import TemplatesIndex from './templates/TemplatesIndex.vue'

export default {
	name: 'Views',
	components: {
		NcAppContent,
		FileIndex,
		Dashboard,
		AnonymizationList,
		SigningIndex,
		WcagIndex,
		TemplatesIndex
	},
}
</script>
