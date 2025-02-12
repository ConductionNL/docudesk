<script setup>
import { signingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ signingStore.signingItem.name }}
					</h1>
					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editSigning')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Signing Bewerken
						</NcActionButton>
						<NcActionButton >
							<template #icon>
								<AccountCheck :size="20" />
							</template>
							Accoderen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteSigning')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Intrekken
						</NcActionButton>
					</NcActions>
				</div>
				<NcNoteCard v-if="signingStore.signingItem.notice" type="info">
					{{ signingStore.signingItem.notice }}
				</NcNoteCard>
				<div class="detailGrid">
					<div>
						<b>Sammenvatting:</b>
						<span>{{ signingStore.signingItem.summary }}</span>
					</div>
				</div>
				<span>{{ signingStore.signingItem.description }}</span>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab active>
							<template #title>
								searchField
							</template>
							
						</BTab>
					</BTabs>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * Component for displaying and managing signing details
 * Includes functionality for editing signings, managing signing properties,
 * skills, items, conditions, events and downloading signings
 */
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcListItem, NcNoteCard, NcCounterBubble } from '@nextcloud/vue'


// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import CalendarPlus from 'vue-material-design-icons/CalendarPlus.vue'
import FileDocumentPlusOutline from 'vue-material-design-icons/FileDocumentPlusOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import EyeArrowRight from 'vue-material-design-icons/EyeArrowRight.vue'
import SwordCross from 'vue-material-design-icons/SwordCross.vue'
import Sword from 'vue-material-design-icons/Sword.vue'
import EmoticonSickOutline from 'vue-material-design-icons/EmoticonSickOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import ShieldSwordOutline from 'vue-material-design-icons/ShieldSwordOutline.vue'
import Download from 'vue-material-design-icons/Download.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import AccountCheck from 'vue-material-design-icons/AccountCheck.vue'

export default {
	name: 'SigningDetails',
	components: {
		// Components
		NcActions,
		NcActionButton,
		NcListItem,
		NcNoteCard,
		NcCounterBubble,
		BTabs,
		BTab,
		// Icons
		DotsHorizontal,
		Pencil,
		AccountPlus,
		CalendarPlus,
		FileDocumentPlusOutline,
		TrashCanOutline,
		EyeArrowRight,
		SwordCross,
		Sword,
		EmoticonSickOutline,
		CalendarMonthOutline,
		ShieldSwordOutline,
		Download,
		BriefcaseAccountOutline,
		AccountCheck,

	},
	methods: {
		downloadSigningPdf() {
			const signingId = signingStore.signingItem.id
			fetch(`signings/${signingId}/download`)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok')
					}
					return response.blob()
				})
				.then(blob => {
					const link = document.createElement('a')
					link.href = window.URL.createObjectURL(blob)
					link.download = `${signingStore.signingItem.name}_signing_sheet.pdf`
					link.click()
					window.URL.revokeObjectURL(link.href)
				})
				.catch(error => {
					console.error('Error downloading PDF:', error)
					// Handle error (e.g., show error message to user)
				})
		},
	},
}
</script>

<style>

h4 {
  font-weight: bold;
}

.head{
	display: flex;
	justify-content: space-between;
}

.button{
	max-height: 10px;
}

.h1 {
  display: block !important;
  font-size: 2em !important;
  margin-block-start: 0.67em !important;
  margin-block-end: 0.67em !important;
  margin-inline-start: 0px !important;
  margin-inline-end: 0px !important;
  font-weight: bold !important;
  unicode-bidi: isolate !important;
}

.dataContent {
  display: flex;
  flex-direction: column;
}

/* Add margin to counter bubble only when inside nav-item */
.nav-item .counter-bubble__counter {
    margin-left: 10px;
}

</style>
