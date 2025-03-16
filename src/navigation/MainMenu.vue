<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<NcAppNavigation>
		<NcAppNavigationList>
			<NcAppNavigationItem :active="navigationStore.selected === 'dashboard'" name="Dashboard" @click="navigationStore.setSelected('dashboard')">
				<template #icon>
					<Finance :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :active="navigationStore.selected === 'files'" name="Files" @click="navigationStore.setSelected('files')">
				<template #icon>
					<FileOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :active="navigationStore.selected === 'anonymization'" name="Anonymization" @click="navigationStore.setSelected('anonymization')">
				<template #icon>
					<Incognito :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :active="navigationStore.selected === 'templates'" name="Templates" @click="navigationStore.setSelected('templates')">
				<template #icon>
					<FileDocumentOutline :size="20" />
				</template>
			</NcAppNavigationItem>
		</NcAppNavigationList>
	</NcAppNavigation>
</template>
<script>

import {
	NcAppNavigation,
	NcAppNavigationList,
	NcAppNavigationSettings,
	NcAppNavigationItem,
} from '@nextcloud/vue'

// Icons
import Finance from 'vue-material-design-icons/Finance.vue'
import Incognito from 'vue-material-design-icons/Incognito.vue'
import FileSign from 'vue-material-design-icons/FileSign.vue'
import CameraDocument from 'vue-material-design-icons/CameraDocument.vue'
import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'

export default {
	name: 'MainMenu',
	components: {
		// components
		NcAppNavigation,
		NcAppNavigationList,
		NcAppNavigationItem,
		// icons
		Finance,
		Incognito,
		FileSign,
		CameraDocument,
		FileDocumentOutline,
		FileOutline
	},
	methods: {
		openLink(url, type = '') {
			window.open(url, type)
		},
	},
}
</script>
