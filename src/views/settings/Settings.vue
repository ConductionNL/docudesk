<template>
	<div>
		<NcSettingsSection
			name="DocuDesk"
			description="A document management system for Nextcloud that helps you organize and share your documents"
			doc-url="https://docudesk.app" />

		<NcSettingsSection
			name="Data storage"
			description="Korte uitleg over dat je kan opslaan in de nextcloud database of open registers en via open registers ook in externe opslag zo al mongo db">
			<div v-if="!loading">
				<!-- Warning about Open Registers not installed -->
				<div v-if="!openRegisterInstalled">
					<NcNoteCard type="info">
						Je hebt nog geen Open Registers geïnstalleerd, we raden je aan om dat wel te doen.
					</NcNoteCard>
					<NcButton type="primary" @click="openLink('/index.php/settings/apps/organization/openregister', '_blank')">
						<template #icon>
							<NcLoadingIcon v-if="loading || saving" :size="20" />
							<Restart v-else :size="20" />
						</template>
						Installeer Open Registers
					</NcButton>
				</div>
				<div v-if="!openRegisterInstalled && hasOpenRegisterSelected">
					<NcNoteCard type="warning">
						Het lijkt erop dat je een open register hebt geselecteerd maar dat deze nog niet geïnstalleerd is. Dit kan problemen geven. Wil je de instelling resetten?
					</NcNoteCard>
					<NcButton type="primary" @click="resetConfig">
						<template #icon>
							<NcLoadingIcon v-if="loading || saving" :size="20" />
							<Restart v-else :size="20" />
						</template>
						Reset
					</NcButton>
				</div>

				<!-- Loop through all object types -->
				<div v-for="type in objectTypes" :key="type">
					<h3>{{ titleMapping(type) }}</h3>
					<div class="selectionContainer">
						<!-- Source dropdown -->
						<NcSelect
							v-bind="labelOptions"
							v-model="sections[type].selectedSource"
							required
							input-label="Source"
							:loading="sections[type].loading"
							:disabled="loading || sections[type].loading"
							@input="onSourceChange(type)" />

						<!-- Register dropdown -->
						<NcSelect
							v-if="sections[type].selectedSource?.value === 'openregister'"
							v-bind="availableRegistersOptions"
							v-model="sections[type].selectedRegister"
							input-label="Register"
							:loading="sections[type].loading"
							:disabled="loading || sections[type].loading"
							@input="onRegisterChange(type)" />

						<!-- Schema dropdown -->
						<NcSelect
							v-if="sections[type].selectedSource?.value === 'openregister' &&
								sections[type].selectedRegister?.value"
							v-bind="globalSchemasOptions[sections[type].selectedRegister.value]"
							v-model="sections[type].selectedSchema"
							input-label="Schema"
							:loading="sections[type].loading"
							:disabled="loading || sections[type].loading" />

						<NcButton
							type="primary"
							:disabled="loading || saving ||
								sections[type].loading ||
								!sections[type].selectedSource?.value ||
								(sections[type].selectedSource?.value === 'openregister' &&
									(!sections[type].selectedRegister?.value || !sections[type].selectedSchema?.value))"
							@click="saveConfig(type)">
							<template #icon>
								<NcLoadingIcon v-if="loading || sections[type].loading" :size="20" />
								<Plus v-else :size="20" />
							</template>
							Opslaan
						</NcButton>
					</div>
				</div>

				<NcButton type="primary" :disabled="saving" @click="saveAll">
					<template #icon>
						<NcLoadingIcon v-if="saving" :size="20" />
						<Plus v-else :size="20" />
					</template>
					Alles opslaan
				</NcButton>
			</div>
			<NcLoadingIcon
				v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				name="Settings aan het laden" />
		</NcSettingsSection>

		<!-- New section for API connections -->
		<NcSettingsSection
			name="API Connections"
			description="Configure connections to external services">
			
			<!-- Presidio Configuration -->
			<div class="api-connection">
				<h3>Presidio API</h3>
				<NcTextField
					v-model="apiConfig.presidio.url"
					label="API URL"
					placeholder="Enter Presidio API URL" />
				<NcTextField
					v-model="apiConfig.presidio.key"
					label="API Key"
					type="password"
					placeholder="Enter Presidio API Key" />
			</div>

			<!-- ChatGPT Configuration -->
			<div class="api-connection">
				<h3>ChatGPT API</h3>
				<NcTextField
					v-model="apiConfig.chatgpt.url" 
					label="API URL"
					placeholder="Enter ChatGPT API URL" />
				<NcTextField
					v-model="apiConfig.chatgpt.key"
					label="API Key"
					type="password" 
					placeholder="Enter ChatGPT API Key" />
			</div>

			<!-- NLDocs Configuration -->
			<div class="api-connection">
				<h3>NLDocs API</h3>
				<NcTextField
					v-model="apiConfig.nldocs.url"
					label="API URL"
					placeholder="Enter NLDocs API URL" />
				<NcTextField
					v-model="apiConfig.nldocs.key"
					label="API Key"
					type="password"
					placeholder="Enter NLDocs API Key" />
			</div>

			<NcButton type="primary" :disabled="saving" @click="saveApiConfig">
				<template #icon>
					<NcLoadingIcon v-if="saving" :size="20" />
					<Plus v-else :size="20" />
				</template>
				Save API Configuration
			</NcButton>
		</NcSettingsSection>
	</div>
</template>

<script>
// Imported components
import { NcSettingsSection, NcNoteCard, NcSelect, NcButton, NcLoadingIcon, NcTextField } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Restart from 'vue-material-design-icons/Restart.vue'

export default {
	name: 'AdminSettings',
	components: {
		NcSettingsSection,
		NcNoteCard,
		NcSelect,
		NcButton,
		NcLoadingIcon,
		NcTextField,
		Plus,
		Restart,
	},
	data() {
		return {
			loading: false,
			saving: false,
			openRegisterInstalled: false,
			initialization: false,
			settingsData: {},
			availableRegisters: [],
			availableRegistersOptions: { options: [] },
			// Global object holding schema options per register.
			globalSchemasOptions: {},
			objectTypes: ['template', 'anonymization', 'report'],
			labelOptions: {
				options: [
					{ label: 'Internal', value: 'internal' },
					{ label: 'OpenRegister', value: 'openregister' },
				],
			},
			// Per‑object settings (e.g. publication, organization, etc.)
			sections: {},
			// API configuration
			apiConfig: {
				presidio: {
					url: '',
					key: ''
				},
				chatgpt: {
					url: '',
					key: ''
				},
				nldocs: {
					url: '',
					key: ''
				}
			}
		}
	},
	computed: {
		// True if any section uses "openregister" as source.
		hasOpenRegisterSelected() {
			return this.objectTypes.some(
				(type) => this.sections[type]?.selectedSource?.value === 'openregister',
			)
		},
	},
	mounted() {
		this.fetchAll()
		this.fetchApiConfig()
	},
	methods: {
		// maps the title to any predefined titles, otherwise just capitalize the first letter and return
		titleMapping(type) {
			const mapping = {
				template: 'Document Template',
				anonymization: 'Anonymization Data',
				report: 'Document Report',
				publicationtype: 'Publicatie type',
				organization: 'Organisatie',
				publication: 'Publicatie',
				theme: 'Thema',
				documentReport: 'Document Report',
				documentLog: 'Document Log'
			}
			return mapping[type] || type.charAt(0).toUpperCase() + type.slice(1)
		},
		// When the source is changed, reassign the entire section object to trigger re-render.
		onSourceChange(type) {
			if (this.sections[type].selectedSource?.value === 'internal') {
				this.sections = {
					...this.sections,
					[type]: {
						...this.sections[type],
						selectedRegister: '',
						selectedSchema: '',
					},
				}
			}
		},
		// When the register is changed, clear the schema by reassigning.
		onRegisterChange(type) {
			this.sections = {
				...this.sections,
				[type]: {
					...this.sections[type],
					selectedSchema: '',
				},
			}
		},
		// Fetch API configuration
		fetchApiConfig() {
			fetch('/index.php/apps/docudesk/api/settings/api-config', { method: 'GET' })
				.then(response => response.json())
				.then(data => {
					this.apiConfig = data
				})
				.catch(err => {
					console.error('Failed to fetch API config:', err)
				})
		},
		// Save API configuration
		saveApiConfig() {
			this.saving = true
			fetch('/index.php/apps/docudesk/api/settings/api-config', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(this.apiConfig)
			})
				.then(response => response.json())
				.then(() => {
					// Show success message
					console.info('API configuration saved successfully')
				})
				.catch(err => {
					console.error('Failed to save API config:', err)
				})
				.finally(() => {
					this.saving = false
				})
		},
		// Fetch all settings and initialize the registers, schemas and sections.
		fetchAll() {
			this.loading = true
			fetch('/index.php/apps/docudesk/api/settings', { method: 'GET' })
				.then((response) => response.json())
				.then((data) => {
					this.initialization = true
					this.openRegisterInstalled = data.openRegisters
					this.settingsData = data
					this.availableRegisters = data.availableRegisters

					// Build available registers options.
					this.availableRegistersOptions = {
						options: data.availableRegisters.map((register) => ({
							value: register.id.toString(),
							label: register.title,
						})),
					}

					// Build global schemas options object per register.
					this.globalSchemasOptions = {}
					data.availableRegisters.forEach((register) => {
						if (register.schemas) {
							this.globalSchemasOptions[register.id.toString()] = {
								options: register.schemas
									// Filter out non-object schemas.
									// When deleting a schema without removing it from a register, it remains as a id
									// This filtering will cause the affected objectType's schema to be non-selected
									.filter((schema) => typeof schema === 'object')
									.map((schema) => ({
										value: schema.id.toString(),
										label: schema.title,
									})),
							}
						}
					})

					// Initialize each section based on object types.
					const newSections = {}
					this.objectTypes.forEach((type) => {
						newSections[type] = {
							// Find the selected source by checking if the source of an Object Type (data[`${type}_source`]) is in the labelOptions.options array.
							// otherwise default to internal. same logic for selectedRegister.
							selectedSource: this.labelOptions.options.find((option) => option.value === data[`${type}_source`]) || { value: 'internal' },
							selectedRegister: this.availableRegistersOptions.options.find((option) => option.value === data[`${type}_register`]) || '',
							selectedSchema: '',
							loading: false,
						}

						// If a register and schema were previously saved, set the schema accordingly.
						if (data[`${type}_register`] && data[`${type}_schema`]) {
							const regId = data[`${type}_register`]
							const opts = this.globalSchemasOptions[regId]
							if (opts) {
								const schemaOption = opts.options.find(
									(opt) => opt.value === data[`${type}_schema`],
								)
								newSections[type].selectedSchema = schemaOption || ''
							}
						}
					})

					this.sections = newSections
					this.initialization = false
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.initialization = false
					this.loading = false
				})
		},
		// Save the configuration for a single object type.
		saveConfig(type) {
			this.sections[type].loading = true
			this.saving = true

			console.info(`Saving ${type} config`)

			const payload = {
				...this.settingsData,
				[`${type}_register`]: this.sections[type].selectedRegister?.value || '',
				[`${type}_schema`]: this.sections[type].selectedSchema?.value || '',
				[`${type}_source`]: this.sections[type].selectedSource?.value || 'internal',
			}

			delete payload.objectTypes
			delete payload.openRegisters
			delete payload.availableRegisters

			fetch('/index.php/apps/docudesk/api/settings', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload),
			})
				.then((response) => response.json())
				.then((data) => {
					this.settingsData = {
						...this.settingsData,
						[`${type}_register`]: data[`${type}_register`],
						[`${type}_schema`]: data[`${type}_schema`],
						[`${type}_source`]: data[`${type}_source`],
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.saving = false
					this.sections[type].loading = false
				})
		},
		// Save all configurations at once.
		saveAll() {
			this.saving = true

			this.objectTypes.forEach((type) => {
				this.sections[type].loading = true
			})

			console.info('Saving all config')

			const payload = { ...this.settingsData }

			this.objectTypes.forEach((type) => {
				payload[`${type}_register`] = this.sections[type].selectedRegister?.value || ''
				payload[`${type}_schema`] = this.sections[type].selectedSchema?.value || ''
				payload[`${type}_source`] = this.sections[type].selectedSource?.value || 'internal'
			})

			delete payload.objectTypes
			delete payload.openRegisters
			delete payload.availableRegisters

			fetch('/index.php/apps/docudesk/api/settings', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload),
			})
				.then((response) => response.json())
				.then((data) => {
					this.settingsData = { ...this.settingsData, ...data }
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.objectTypes.forEach((type) => {
						this.sections[type].loading = false
					})
					this.saving = false
				})
		},
		// Reset all configurations.
		resetConfig() {
			this.saving = true

			const payload = { ...this.settingsData }

			this.objectTypes.forEach((type) => {
				payload[`${type}_register`] = ''
				payload[`${type}_schema`] = ''
				payload[`${type}_source`] = 'internal'
			})

			delete payload.objectTypes
			delete payload.openRegisters
			delete payload.availableRegisters

			fetch('/index.php/apps/docudesk/api/settings', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload),
			})
				.then((response) => response.json())
				.then(() => {
					this.fetchAll()
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.saving = false
				})
		},
		openLink(url, target = '') {
			window.open(url, target)
		},
	},
}
</script>

<style>
.selectionContainer {
    display: grid;
    grid-gap: 5px;
    grid-template-columns: 1fr;
}
.selectionContainer > * {
    margin-block-end: 10px;
}

.api-connection {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid var(--color-border);
    border-radius: 8px;
}

.api-connection h3 {
    margin-top: 0;
    margin-bottom: 15px;
}
</style>
