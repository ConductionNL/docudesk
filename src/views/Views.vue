<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Main content container for all views -->
	<NcAppContent>
		<template #default>
			<Dashboard v-if="navigationStore.selected === 'dashboard'" />
			<FileIndex v-if="navigationStore.selected === 'files'" />
			<AnonymizationIndex v-if="navigationStore.selected === 'anonymization'" />
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
import Dashboard from './dashboard/DashboardIndex.vue'
import FileIndex from './files/FileIndex.vue'
import AnonymizationIndex from './anonymization/AnonymizationIndex.vue'
import TemplatesIndex from './templates/TemplatesIndex.vue'

export default {
	name: 'Views',
	components: {
		NcAppContent,
		Dashboard,
		FileIndex,
		AnonymizationIndex,
		TemplatesIndex
	},
}
</script>
