<script setup>
import { wcagStore, navigationStore, searchStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="wcagStore.searchTerm"
					:show-trailing-button="wcagStore.searchTerm !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@input="wcagStore.setSearchTerm($event.target.value)"
					@trailing-button-click="wcagStore.clearSearch()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="wcagStore.refreshWcagList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="wcagStore.setWcagItem(null); navigationStore.setModal('editWcag')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add WCAG
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="wcagStore.wcagList && wcagStore.wcagList.length > 0 && !wcagStore.isLoadingWcagList">
				<NcListItem v-for="(wcag, i) in wcagStore.wcagList"
					:key="`${wcag}${i}`"
					:name="wcag?.name"
					:force-display-actions="true"
					:active="wcagStore.wcagItem?.id === wcag?.id"
					:details="wcag.approved === 'approved' ? 'Approved': 'Not approved'"
					:counter-number="wcag?.skills?.length || 0"
					@click="handleWcagSelect(wcag)">
					<template #icon>
						<BriefcaseAccountOutline :class="wcagStore.wcagItem?.id === wcag?.id && 'selectedIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ wcag?.summary || 'No summary available' }}
					</template>
					<template #actions>
						<NcActionButton @click="wcagStore.setWcagItem(wcag); navigationStore.setModal('editWcag')">
							<template #icon>
								<Pencil />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="wcagStore.setWcagItem(wcag); navigationStore.setDialog('deleteWcag')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Delete
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="wcagStore.isLoadingWcagList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading WCAG items" />

		<div v-if="wcagStore.wcagList.length === 0 && !wcagStore.isLoadingWcagList">
			No WCAG items defined yet.
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of WCAG items
 * Provides functionality for searching, adding, editing and deleting WCAG items
 */
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'WcagList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		BriefcaseAccountOutline,
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	mounted() {
		wcagStore.refreshWcagList()
	},
	methods: {
		/**
		 * Handle WCAG item selection
		 * @param {object} wcag - The selected WCAG object
		 */
		async handleWcagSelect(wcag) {
			// Set the selected WCAG item in the store
			wcagStore.setWcagItem(wcag)
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
