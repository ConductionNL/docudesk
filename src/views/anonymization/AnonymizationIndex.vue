<script setup>
import { objectStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<AnonymizationList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!objectStore.getActiveObject('anonymization') || navigationStore.selected !== 'anonymization'"
				class="detailContainer" 
				name="No Documents"
				description="No documents selected for anonymization">
				<template #icon>
					<Incognito />
				</template>
				<template #action>
					<NcButton type="primary" @click="objectStore.setActiveObject('anonymization', null); navigationStore.setModal('editAnonymization')">
						Add Document
					</NcButton>
				</template>
			</NcEmptyContent>
			<AnonymizationDetails v-if="objectStore.getActiveObject('anonymization') && navigationStore.selected === 'anonymization'" />
		</template>
	</NcAppContent>
</template>

<script>
/**
 * Main component for the anonymization view that handles displaying the list of documents
 * and their anonymization details
 * 
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import AnonymizationList from './AnonymizationList.vue'
import AnonymizationDetails from './AnonymizationDetails.vue'
import Incognito from 'vue-material-design-icons/Incognito.vue'

export default {
	name: 'AnonymizationIndex',
	components: {
		NcAppContent,
		NcEmptyContent, 
		NcButton,
		AnonymizationList,
		AnonymizationDetails,
		Incognito,
	},
}
</script>
