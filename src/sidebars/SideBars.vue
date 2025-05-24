/**
 * SideBars component that manages reports sidebars
 *
 * @category Vue Components
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 * @link https://github.com/ConductionNL/docudesk
 */

<template>
	<div class="sidebars-container">
		<!-- Report-related sidebars -->
		<ReportsSideBar v-if="navigationStore.sidebarState.reports" />
		<ReportSideBar v-if="navigationStore.sidebarState.report" />
	</div>
</template>

<script>
import { navigationStore } from '../store/store.js'
import ReportsSideBar from './reports/ReportsSideBar.vue'
import ReportSideBar from './reports/ReportSideBar.vue'

export default {
	name: 'SideBars',
	components: {
		ReportsSideBar,
		ReportSideBar,
	},
	data() {
		return {
			navigationStore,
		}
	},
}
</script>

<style scoped>
.sidebars-container {
	position: relative;
	z-index: 1000;
}
</style>
