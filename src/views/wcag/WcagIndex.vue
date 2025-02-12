<script setup>
import { wcagStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<WcagList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!wcagStore.wcagItem || navigationStore.selected != 'wcag' "
				class="detailContainer"
				name="No WCAG"
				description="No WCAG item selected">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="wcagStore.setWcagItem(null); navigationStore.setModal('editWcag')">
						Add WCAG
					</NcButton>
				</template>
			</NcEmptyContent>
			<WcagDetails v-if="wcagStore.wcagItem && navigationStore.selected === 'wcag'" />
		</template>
	</NcAppContent>
</template>

<script>
/**
 * Main component for the WCAG view that handles displaying the list of WCAG items
 * and their details
 */
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import WcagList from './WcagList.vue'
import WcagDetails from './WcagDetails.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

export default {
	name: 'WcagIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		WcagList,
		WcagDetails,
		BriefcaseAccountOutline,
	},
}
</script>
