<script setup>
import { signingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<SigningList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!signingStore.signingItem || navigationStore.selected != 'signing' "
				class="detailContainer"
				name="No Signing"
				description="No signing selected">
				<template #icon>
					<FileDocumentOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="signingStore.setSigningItem(null); navigationStore.setModal('editSigning')">
						Create Signing
					</NcButton>
				</template>
			</NcEmptyContent>
			<SigningDetails v-if="signingStore.signingItem && navigationStore.selected === 'signing'" />
		</template>
	</NcAppContent>
</template>

<script>
/**
 * Main signing view component that handles the layout and routing between list and detail views
 * Contains the signing list and detail components and handles empty state
 */
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import SigningList from './SigningList.vue'
import SigningDetails from './SigningDetails.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'

export default {
	name: 'SigningIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		SigningList,
		SigningDetails,
		FileDocumentOutline,
	},
}
</script>
