<script setup>
import { anonymizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="anonymizationStore.searchTerm"
					:show-trailing-button="anonymizationStore.searchTerm !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@input="anonymizationStore.setSearchTerm($event.target.value)"
					@trailing-button-click="anonymizationStore.clearSearch()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="anonymizationStore.refreshAnonymizationList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="anonymizationStore.setAnonymizationItem(null); navigationStore.setModal('editAnonymization')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add Document
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="anonymizationStore.anonymizationList && anonymizationStore.anonymizationList.length > 0 && !anonymizationStore.isLoadingAnonymizationList">
				<NcListItem v-for="(document, i) in anonymizationStore.anonymizationList"
					:key="`${document}${i}`"
					:name="document?.name || 'Unnamed Document'"
					:force-display-actions="true"
					:active="anonymizationStore.anonymizationItem?.id === document?.id"
					:counter-number="document?.rules?.length || 0"
					@click="handleAnonymizationSelect(document)">
					<template #icon>
						<Incognito :class="anonymizationStore.anonymizationItem?.id === document?.id && 'selectedIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ document?.summary || 'No summary available' }}
					</template>
					<template #actions>
						<NcActionButton @click="anonymizationStore.setAnonymizationItem(document); navigationStore.setModal('editAnonymization')">
							<template #icon>
								<Pencil />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="anonymizationStore.setAnonymizationItem(document); navigationStore.setDialog('deleteAnonymization')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Delete
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="anonymizationStore.isLoadingAnonymizationList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading documents" />

		<div v-if="anonymizationStore.anonymizationList.length === 0 && !anonymizationStore.isLoadingAnonymizationList">
			No documents have been added for anonymization yet.
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of documents for anonymization
 */
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Incognito from 'vue-material-design-icons/Incognito.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'AnonymizationList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		Incognito,
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	mounted() {
		anonymizationStore.refreshAnonymizationList()
	},
	methods: {
		/**
		 * Handle document selection for anonymization
		 * @param {object} document - The selected document object
		 */
		async handleAnonymizationSelect(document) {
			// Set the selected document in the store
			anonymizationStore.setAnonymizationItem(document)
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
