<script setup>
import { reportStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="reportStore.searchTerm"
					:show-trailing-button="reportStore.searchTerm !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@input="reportStore.setSearchTerm($event.target.value)"
					@trailing-button-click="reportStore.clearSearch()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="reportStore.refreshReportList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="reportStore.setReportItem(null); navigationStore.setModal('editReport')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add Report
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="reportStore.reportList && reportStore.reportList.length > 0 && !reportStore.isLoadingReportList">
				<NcListItem v-for="(report, i) in reportStore.reportList"
					:key="`${report}${i}`"
					:name="report?.name"
					:force-display-actions="true"
					:active="reportStore.reportItem?.id === report?.id"
					:counter-number="report?.rules?.length || 0"
					@click="handleReportSelect(report)">
					<template #icon>
						<File :class="reportStore.reportItem?.id === report?.id && 'selectedIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ report?.summary || 'No summary available' }}
					</template>
					<template #actions>
						<NcActionButton @click="reportStore.setReportItem(report); navigationStore.setModal('editReport')">
							<template #icon>
								<Pencil />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="reportStore.setReportItem(report); navigationStore.setDialog('deleteReport')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Delete
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="reportStore.isLoadingReportList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading reports" />

		<div v-if="reportStore.reportList.length === 0 && !reportStore.isLoadingReportList">
			No reports have been added yet.
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of reports
 * 
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
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
		reportStore.refreshReportList()
	},
	methods: {
		/**
		 * Handle report selection
		 * @param {object} report - The selected report object
		 */
		async handleReportSelect(report) {
			// Set the selected report in the store
			reportStore.setReportItem(report)
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
