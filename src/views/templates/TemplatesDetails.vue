<script setup>
import { templateStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="head">
					<h1 class="h1">
						{{ templateStore.templateItem.name }}
					</h1>
					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editTemplate')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit Template
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addSkillToTemplate')">
							<template #icon>
								<FileOutline :size="20" />
							</template>
							Edit Skills
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addItemToTemplate')">
							<template #icon>
								<AccountPlus :size="20" />
							</template>
							Edit Items
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addConditionToTemplate')">
							<template #icon>
								<EmoticonSickOutline :size="20" />
							</template>
							Edit Conditions
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addEventToTemplate')">
							<template #icon>
								<CalendarPlus :size="20" />
							</template>
							Edit Events
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('renderPdfFromTemplate')">
							<template #icon>
								<Download :size="20" />
							</template>
							Download as PDF
						</NcActionButton>
						<NcActionButton>
							<template #icon>
								<AccountCheck :size="20" />
							</template>
							Approve
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteTemplate')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
				<NcNoteCard v-if="templateStore.templateItem.notice" type="info">
					{{ templateStore.templateItem.notice }}
				</NcNoteCard>
				<div class="detailGrid">
					<div>
						<b>Summary:</b>
						<span>{{ templateStore.templateItem.summary }}</span>
					</div>
				</div>
				<span>{{ templateStore.templateItem.description }}</span>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab active>
							<template #title>
								return
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
 * Component for displaying and managing template details
 * Includes functionality for editing templates, managing template properties,
 * skills, items, conditions, events and downloading templates
 */
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcListItem, NcNoteCard, NcCounterBubble } from '@nextcloud/vue'


// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import CalendarPlus from 'vue-material-design-icons/CalendarPlus.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'
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
	name: 'TemplateDetails',
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
		FileOutline,
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
		downloadTemplatePdf() {
			const templateId = templateStore.templateItem.id
			fetch(`templates/${templateId}/download`)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok')
					}
					return response.blob()
				})
				.then(blob => {
					const link = document.createElement('a')
					link.href = window.URL.createObjectURL(blob)
					link.download = `${templateStore.templateItem.name}_template_sheet.pdf`
					link.click()
					window.URL.revokeObjectURL(link.href)
				})
				.catch(error => {
					console.error('Error downloading PDF:', error)
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
