<script setup>
import { objectStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="objectStore.getSearchTerm('anonymization')"
					:show-trailing-button="objectStore.getSearchTerm('anonymization') !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@update:value="(value) => objectStore.setSearchTerm('anonymization', value)"
					@trailing-button-click="objectStore.clearSearch('anonymization')">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="objectStore.fetchCollection('anonymization')">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="objectStore.clearActiveObject('anonymization'); navigationStore.setModal('editAnonymization')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add Document
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="!objectStore.isLoading('anonymization')">
				<NcListItem v-for="(document, i) in objectStore.getCollection('anonymization').results"
					:key="`${document}${i}`"
					:name="document?.originalFileName || 'Unnamed Document'"
					:force-display-actions="true"
					:active="objectStore.getActiveObject('anonymization')?.id === document?.id"
					:counter-number="document?.entities?.length || 0"
					@click="handleAnonymizationSelect(document)">
					<template #icon>
						<Incognito :class="objectStore.getActiveObject('anonymization')?.id === document?.id && 'selectedIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						<div class="document-status">
							<span>Status: {{ document?.status || 'unknown' }}</span>
							<span v-if="document?.anonymizedFileName">| Anonymized: {{ document.anonymizedFileName }}</span>
						</div>
					</template>
					<template #actions>
						<NcActionButton @click="objectStore.setActiveObject('anonymization', document); navigationStore.setModal('editAnonymization')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="objectStore.setActiveObject('anonymization', document); navigationStore.setDialog('deleteObject', { objectType: 'anonymization', dialogTitle: 'Document' })">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="objectStore.isLoading('anonymization')"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading documents" />

		<div v-if="!objectStore.getCollection('anonymization').results.length" class="empty-state">
			<p>No documents have been added for anonymization yet.</p>
			<NcButton type="primary" @click="objectStore.clearActiveObject('anonymization'); navigationStore.setModal('editAnonymization')">
				Add Document
			</NcButton>
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of documents for anonymization
 *
 * @package
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcButton } from '@nextcloud/vue'

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
		NcButton,
		// Icons
		Incognito,
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	mounted() {
		objectStore.fetchCollection('anonymization')
	},
	methods: {
		/**
		 * Handle document selection for anonymization
		 * @param {object} document - The selected document object
		 */
		async handleAnonymizationSelect(document) {
			// Set the selected document in the store
			objectStore.setActiveObject('anonymization', document)
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
    display: flex;
    flex-direction: row;
    align-items: center;
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

.document-status {
    display: flex;
    gap: 8px;
    color: var(--color-text-maxcontrast);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    text-align: center;
    color: var(--color-text-maxcontrast);
    gap: 16px;
}
</style>
