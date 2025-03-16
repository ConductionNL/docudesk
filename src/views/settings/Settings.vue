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
				<div class="input-field">
					<label for="presidio-analyzer-url">Analyzer API URL</label>
					<input 
						id="presidio-analyzer-url"
						v-model="apiConfig.presidio.analyzerUrl" 
						type="text"
						placeholder="Enter Presidio Analyzer API URL (e.g., http://presidio-api:8080/analyze)" />
				</div>
				<div class="input-field">
					<label for="presidio-anonymizer-url">Anonymizer API URL</label>
					<input 
						id="presidio-anonymizer-url"
						v-model="apiConfig.presidio.anonymizerUrl" 
						type="text"
						placeholder="Enter Presidio Anonymizer API URL (e.g., http://presidio-api:8080/anonymize)" />
				</div>
				<div class="input-field">
					<label for="presidio-key">API Key</label>
					<input 
						id="presidio-key"
						v-model="apiConfig.presidio.key" 
						type="password"
						placeholder="Enter Presidio API Key" />
				</div>
			</div>

			<!-- ChatGPT Configuration -->
			<div class="api-connection">
				<h3>ChatGPT API</h3>
				<div class="input-field">
					<label for="chatgpt-url">API URL</label>
					<input 
						id="chatgpt-url"
						v-model="apiConfig.chatgpt.url" 
						type="text"
						placeholder="Enter ChatGPT API URL" />
				</div>
				<div class="input-field">
					<label for="chatgpt-key">API Key</label>
					<input 
						id="chatgpt-key"
						v-model="apiConfig.chatgpt.key" 
						type="password"
						placeholder="Enter ChatGPT API Key" />
				</div>
			</div>

			<!-- NLDocs Configuration -->
			<div class="api-connection">
				<h3>NLDocs API</h3>
				<div class="input-field">
					<label for="nldocs-url">API URL</label>
					<input 
						id="nldocs-url"
						v-model="apiConfig.nldocs.url" 
						type="text"
						placeholder="Enter NLDocs API URL" />
				</div>
				<div class="input-field">
					<label for="nldocs-key">API Key</label>
					<input 
						id="nldocs-key"
						v-model="apiConfig.nldocs.key" 
						type="password"
						placeholder="Enter NLDocs API Key" />
				</div>
			</div>

			<NcButton type="primary" :disabled="saving" @click="saveApiConfig">
				<template #icon>
					<NcLoadingIcon v-if="saving" :size="20" />
					<Plus v-else :size="20" />
				</template>
				Save API Configuration
			</NcButton>
		</NcSettingsSection>

		<!-- Report Configuration Section -->
		<NcSettingsSection :title="t('docudesk', 'Report Configuration')" :description="t('docudesk', 'Configure document report generation settings')">
			<div class="report-config-section">
				<div class="report-config-item">
					<label for="enable-reporting">{{ t('docudesk', 'Enable Reporting') }}</label>
					<input type="checkbox" id="enable-reporting" v-model="reportConfig.enable_reporting" />
					<span class="report-config-description">{{ t('docudesk', 'Enable automatic report generation for documents') }}</span>
				</div>
				
				<div class="report-config-item">
					<label for="synchronous-processing">{{ t('docudesk', 'Synchronous Processing') }}</label>
					<input type="checkbox" id="synchronous-processing" v-model="reportConfig.synchronous_processing" />
					<span class="report-config-description">{{ t('docudesk', 'Process reports immediately instead of using background jobs') }}</span>
				</div>
				
				<div class="report-config-item">
					<label for="confidence-threshold">{{ t('docudesk', 'Confidence Threshold') }}</label>
					<input type="range" id="confidence-threshold" v-model.number="reportConfig.confidence_threshold" min="0" max="1" step="0.05" />
					<span class="threshold-value">{{ (reportConfig.confidence_threshold * 100).toFixed(0) }}%</span>
					<span class="report-config-description">{{ t('docudesk', 'Minimum confidence level for entity detection') }}</span>
				</div>
				
				<div class="report-config-item">
					<label for="store-original-text">{{ t('docudesk', 'Store Original Text') }}</label>
					<input type="checkbox" id="store-original-text" v-model="reportConfig.store_original_text" />
					<span class="report-config-description">{{ t('docudesk', 'Store the original document text in reports') }}</span>
				</div>
				
				<div class="report-config-item">
					<button @click="saveReportConfig" :disabled="isSavingReportConfig">
						{{ t('docudesk', 'Save Report Configuration') }}
					</button>
				</div>
			</div>
		</NcSettingsSection>
	</div>
</template>

<script>
// Imported components
import { NcSettingsSection, NcNoteCard, NcSelect, NcButton, NcLoadingIcon, NcTextField } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Restart from 'vue-material-design-icons/Restart.vue'

/**
 * AdminSettings component for DocuDesk
 * 
 * @component
 */
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
	/**
	 * Component data
	 * 
	 * @returns {Object} Component data
	 */
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
			// Define the object types we want to configure
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
					analyzerUrl: '',
					anonymizerUrl: '',
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
			},
			reportConfig: {
				enable_reporting: true,
				synchronous_processing: false,
				confidence_threshold: 0.7,
				store_original_text: true,
				report_object_type: 'report',
				log_object_type: 'documentLog',
			},
			isSavingReportConfig: false,
		}
	},
	computed: {
		/**
		 * Check if any section uses "openregister" as source
		 * 
		 * @returns {boolean} True if any section uses "openregister" as source
		 */
		hasOpenRegisterSelected() {
			return this.objectTypes.some(
				(type) => this.sections[type]?.selectedSource?.value === 'openregister',
			)
		},
	},
	/**
	 * Component mounted lifecycle hook
	 * 
	 * @returns {void}
	 */
	mounted() {
		this.fetchAll()
		this.fetchApiConfig()
		this.fetchReportConfig()
	},
	methods: {
		/**
		 * Maps the title to any predefined titles, otherwise just capitalize the first letter and return
		 * 
		 * @param {string} type - The type to map
		 * @returns {string} The mapped title
		 */
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
		/**
		 * When the source is changed, reassign the entire section object to trigger re-render
		 * 
		 * @param {string} type - The type to update
		 * @returns {void}
		 */
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
		/**
		 * When the register is changed, clear the schema by reassigning
		 * 
		 * @param {string} type - The type to update
		 * @returns {void}
		 */
		onRegisterChange(type) {
			this.sections = {
				...this.sections,
				[type]: {
					...this.sections[type],
					selectedSchema: '',
				},
			}
		},
		/**
		 * Fetch API configuration
		 * 
		 * @returns {void}
		 */
		fetchApiConfig() {
			fetch('/index.php/apps/docudesk/api/settings/api-config', { method: 'GET' })
				.then(response => response.json())
				.then(data => {
					// Simple assignment without any validation or conversion
					this.apiConfig = data;
				})
				.catch(err => {
					console.error('Failed to fetch API config:', err)
				})
		},
		/**
		 * Save API configuration
		 * 
		 * @returns {void}
		 */
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
		/**
		 * Fetch all settings and initialize the registers, schemas and sections
		 * 
		 * @returns {void}
		 */
		fetchAll() {
			this.loading = true
			fetch('/index.php/apps/docudesk/api/settings', { method: 'GET' })
				.then((response) => response.json())
				.then((data) => {
					this.initialization = true
					this.openRegisterInstalled = data.openRegisters
					this.settingsData = data
					this.availableRegisters = data.availableRegisters

					// Ensure our required object types are included
					if (!data.objectTypes || !data.objectTypes.includes('template')) {
						console.info('Adding template to object types')
						if (!data.objectTypes) {
							data.objectTypes = []
						}
						if (!data.objectTypes.includes('template')) {
							data.objectTypes.push('template')
						}
					}
					if (!data.objectTypes.includes('anonymization')) {
						console.info('Adding anonymization to object types')
						data.objectTypes.push('anonymization')
					}
					if (!data.objectTypes.includes('report')) {
						console.info('Adding report to object types')
						data.objectTypes.push('report')
					}

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
					// Use our predefined object types instead of data.objectTypes
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
		/**
		 * Save the configuration for a single object type
		 * 
		 * @param {string} type - The type to save
		 * @returns {void}
		 */
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
		/**
		 * Save all configurations at once
		 * 
		 * @returns {void}
		 */
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
		/**
		 * Reset all configurations
		 * 
		 * @returns {void}
		 */
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
		/**
		 * Open a link in a new window or tab
		 * 
		 * @param {string} url - The URL to open
		 * @param {string} target - The target for the link
		 * @returns {void}
		 */
		openLink(url, target = '') {
			window.open(url, target)
		},
		async fetchReportConfig() {
			try {
				const response = await axios.get(generateUrl('/apps/docudesk/api/v1/settings/report'))
				this.reportConfig = response.data
			} catch (error) {
				console.error('Error fetching report configuration:', error)
				showError(t('docudesk', 'Failed to load report configuration'))
			}
		},
		async saveReportConfig() {
			try {
				this.isSavingReportConfig = true
				await axios.post(generateUrl('/apps/docudesk/api/v1/settings/report'), this.reportConfig)
				showSuccess(t('docudesk', 'Report configuration saved successfully'))
			} catch (error) {
				console.error('Error saving report configuration:', error)
				showError(t('docudesk', 'Failed to save report configuration'))
			} finally {
				this.isSavingReportConfig = false
			}
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

.input-field {
    margin-bottom: 15px;
}

.input-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.input-field input {
    width: 100%;
    padding: 8px;
    border: 1px solid var(--color-border);
    border-radius: 4px;
    background-color: var(--color-main-background);
    color: var(--color-main-text);
}

.report-config-section {
	display: flex;
	flex-direction: column;
	gap: 16px;
	margin-top: 16px;
}

.report-config-item {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.report-config-item label {
	font-weight: bold;
}

.report-config-description {
	color: var(--color-text-lighter);
	font-size: 0.9em;
}

.threshold-value {
	margin-left: 8px;
	font-weight: bold;
}
</style>
