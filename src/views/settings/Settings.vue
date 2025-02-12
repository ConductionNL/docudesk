<template>
	<div>
		<NcSettingsSection 
			name="DocuDesk" 
			description="A central place for managing your LARP characters and game elements" 
			doc-url="https://conduction.gitbook.io/larpingapp-nextcloud/users" />
		
		<NcSettingsSection 
			name="Data storage" 
			description="Configure where to store your LARP data">
			<div v-if="!loading">
				<!-- Warning if OpenRegister is not installed but selected -->
				<NcNoteCard v-if="!settings.openRegisters" type="warning">
					Open Register is not installed. Some features might be unavailable.
				</NcNoteCard>

				<!-- Object Type Configuration -->
				<div v-for="objectType in settings.objectTypes" :key="objectType" class="object-type-section">
					<h3>{{ formatTitle(objectType) }}</h3>
					
					<div class="selection-container">
						<!-- Source Selection -->
						<NcSelect
							v-model="configuration[objectType].source"
							:options="sourceOptions"
							input-label="Source"
							:disabled="loading"
							@change="handleSourceChange(objectType)" />

						<!-- Register Selection (only if OpenRegister is selected) -->
						<NcSelect
							v-if="configuration[objectType].source?.value === 'openregister'"
							v-model="configuration[objectType].register"
							:options="registerOptions"
							input-label="Register"
							:disabled="loading"
							@change="handleRegisterChange(objectType)" />

						<!-- Schema Selection (only if Register is selected) -->
						<NcSelect
							v-if="configuration[objectType].source?.value === 'openregister' && configuration[objectType].register"
							v-model="configuration[objectType].schema"
							:options="getSchemaOptions(configuration[objectType].register?.value)"
							input-label="Schema"
							:disabled="loading" />
					</div>
				</div>

				<!-- Save Buttons -->
				<div class="button-container">
					<NcButton
						type="primary"
						:disabled="loading || saving"
						@click="saveAll">
						<template #icon>
							<NcLoadingIcon v-if="saving" :size="20" />
							<Save v-else :size="20" />
						</template>
						Save All
					</NcButton>
				</div>
			</div>

			<!-- Loading State -->
			<NcLoadingIcon v-else
				class="loading-icon"
				:size="64"
				appearance="dark" />
		</NcSettingsSection>
	</div>
</template>

<script>
import { defineComponent } from 'vue'
import { 
	NcSettingsSection, 
	NcNoteCard, 
	NcSelect, 
	NcButton, 
	NcLoadingIcon 
} from '@nextcloud/vue'
import Save from 'vue-material-design-icons/ContentSave.vue'

export default defineComponent({
	name: 'Settings',
	components: {
		NcSettingsSection,
		NcNoteCard,
		NcSelect,
		NcButton,
		NcLoadingIcon,
		Save,
	},

	data() {
		return {
			loading: true,
			saving: false,
			settings: {
				objectTypes: [],
				openRegisters: false,
				availableRegisters: [],
				configuration: {},
			},
			configuration: {},
			sourceOptions: [
				{ label: 'Internal', value: 'internal' },
				{ label: 'Open Register', value: 'openregister' },
			],
		}
	},

	computed: {
		registerOptions() {
			return this.settings.availableRegisters.map(register => ({
				label: register.title,
				value: register.id.toString(),
			}))
		},
	},

	async created() {
		await this.loadSettings()
	},

	methods: {
		async loadSettings() {
			try {
				const response = await fetch('/index.php/apps/larpingapp/api/settings')
				const data = await response.json()
				this.settings = data

				// Initialize configuration for each object type based on existing config
				this.settings.objectTypes.forEach(type => {
					const source = this.settings.configuration[`${type}_source`] || 'internal'
					const registerId = this.settings.configuration[`${type}_register`]
					const schemaId = this.settings.configuration[`${type}_schema`]

					this.configuration[type] = {
						source: this.sourceOptions.find(option => option.value === source),
						register: registerId ? {
							label: this.getRegisterLabel(registerId),
							value: registerId
						} : null,
						schema: schemaId ? {
							label: this.getSchemaLabel(registerId, schemaId),
							value: schemaId
						} : null,
					}
				})

				this.loading = false
			} catch (error) {
				console.error('Failed to load settings:', error)
			}
		},

		getRegisterLabel(registerId) {
			const register = this.settings.availableRegisters.find(r => r.id.toString() === registerId)
			return register?.title || ''
		},

		getSchemaLabel(registerId, schemaId) {
			const register = this.settings.availableRegisters.find(r => r.id.toString() === registerId)
			const schema = register?.schemas.find(s => s.id.toString() === schemaId)
			return schema?.title || ''
		},

		formatTitle(objectType) {
			return objectType.charAt(0).toUpperCase() + objectType.slice(1)
		},

		getSchemaOptions(registerId) {
			if (!registerId) return []
			const register = this.settings.availableRegisters.find(r => r.id.toString() === registerId)
			return register?.schemas.map(schema => ({
				label: schema.title,
				value: schema.id.toString(),
			})) || []
		},

		handleSourceChange(objectType) {
			const config = this.configuration[objectType]
			if (config.source.value === 'internal') {
				config.register = null
				config.schema = null
			}
		},

		handleRegisterChange(objectType) {
			this.configuration[objectType].schema = null
		},

		async saveAll() {
			this.saving = true
			try {
				const configToSave = {}
				
				// Convert configuration to flat structure
				Object.entries(this.configuration).forEach(([type, config]) => {
					configToSave[`${type}_source`] = config.source.value
					if (config.source.value === 'openregister') {
						if (config.register) {
							configToSave[`${type}_register`] = config.register.value
						}
						if (config.schema) {
							configToSave[`${type}_schema`] = config.schema.value
						}
					}
				})

				await fetch('/index.php/apps/larpingapp/api/settings', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(configToSave),
				})
			} catch (error) {
				console.error('Failed to save settings:', error)
			} finally {
				this.saving = false
			}
		},
	},
})
</script>

<style scoped>
.object-type-section {
	margin-bottom: 2rem;
}

.selection-container {
	display: flex;
	gap: 1rem;
	align-items: flex-start;
	margin-top: 0.5rem;
}

.button-container {
	margin-top: 2rem;
}

.loading-icon {
	display: flex;
	justify-content: center;
	margin: 2rem 0;
}
</style>
