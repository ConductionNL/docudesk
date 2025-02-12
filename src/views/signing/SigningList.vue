<script setup>
import { signingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value="signingStore.searchTerm"
					:show-trailing-button="signingStore.searchTerm !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@input="signingStore.setSearchTerm($event.target.value)"
					@trailing-button-click="signingStore.clearSearch()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="signingStore.refreshSigningList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="signingStore.setSigningItem(null); navigationStore.setModal('editSigning')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Signing toevoegen
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="signingStore.signingList && signingStore.signingList.length > 0 && !signingStore.isLoadingSigningList">
				<NcListItem v-for="(signing, i) in signingStore.signingList"
					:key="`${signing}${i}`"
					:name="signing?.name"
					:force-display-actions="true"
					:active="signingStore.signingItem?.id === signing?.id"
					:details="signing.approved === 'approved' ? 'Approved': 'Not approved'"
					:counter-number="signing?.skills?.length || 0"
					@click="handleSigningSelect(signing)">
					<template #icon>
						<BriefcaseAccountOutline :class="signingStore.signingItem?.id === signing?.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ signing?.ocName?.name || 'No player selected' }}
					</template>
					<template #actions>
						<NcActionButton @click="signingStore.setSigningItem(signing); navigationStore.setModal('editSigning')">
							<template #icon>
								<Pencil />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="signingStore.setSigningItem(signing); navigationStore.setDialog('deleteSigning')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="signingStore.isLoadingSigningList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Signings aan het laden" />

		<div v-if="signingStore.signingList.length === 0 && !signingStore.isLoadingSigningList">
			Er zijn nog geen signings gedefinieerd.
		</div>
	</NcAppContentList>
</template>

<script>
/**
 * Component for displaying and managing the list of signings
 * Includes functionality for searching, refreshing, adding, editing and deleting signings
 */
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'SigningList',
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
		signingStore.refreshSigningList()
	},
	methods: {
		/**
		 * Handle signing selection
		 * @param {object} signing - The selected signing object
		 */
		async handleSigningSelect(signing) {
			// Set the selected signing in the store
			signingStore.setSigningItem(signing)
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
