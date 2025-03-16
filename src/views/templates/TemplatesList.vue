<script setup>
import { templateStore, navigationStore, searchStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="templateStore.searchTerm"
					:show-trailing-button="templateStore.searchTerm !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@input="templateStore.setSearchTerm($event.target.value)"
					@trailing-button-click="templateStore.clearSearch()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="templateStore.refreshTemplateList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Refresh
					</NcActionButton>
					<NcActionButton @click="templateStore.setTemplateItem(null); navigationStore.setModal('editTemplate')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add Template
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="templateStore.templateList && templateStore.templateList.length > 0 && !templateStore.isLoadingTemplateList">
				<NcListItem v-for="(template, i) in templateStore.templateList"
					:key="`${template}${i}`"
					:name="template?.name || 'Unnamed Template'"
					:force-display-actions="true"
					:active="templateStore.templateItem?.id === template?.id"
					:details="template.approved === 'approved' ? 'Approved': 'Not approved'"
					:counter-number="template?.skills?.length || 0"
					@click="handleTemplateSelect(template)">
					<template #icon>
						<BriefcaseAccountOutline :class="templateStore.templateItem?.id === template?.id && 'selectedIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ template?.summary || 'No summary available' }}
					</template>
					<template #actions>
						<NcActionButton @click="templateStore.setTemplateItem(template); navigationStore.setModal('editTemplate')">
							<template #icon>
								<Pencil />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="templateStore.setTemplateItem(template); navigationStore.setDialog('deleteTemplate')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Delete
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="templateStore.isLoadingTemplateList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Loading templates" />

		<div v-if="templateStore.templateList.length === 0 && !templateStore.isLoadingTemplateList">
			No templates defined yet.
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of templates
 * Provides functionality for searching, adding, editing and deleting templates
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
	name: 'TemplatesList',
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
		templateStore.refreshTemplateList()
	},
	methods: {
		/**
		 * Handle template selection
		 * @param {object} template - The selected template object
		 */
		async handleTemplateSelect(template) {
			// Set the selected template in the store
			templateStore.setTemplateItem(template)
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
