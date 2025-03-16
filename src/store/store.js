/* eslint-disable no-console */
// The store script handles app wide variables (or state), for the use of these variables and there governing concepts read the design.md
import pinia from '../pinia.js'
import { useAnonymizationStore } from './modules/anonymization.js'
import { useNavigationStore } from './modules/navigation.ts'
import { useReportStore } from './modules/report.js'
import { useTemplateStore } from './modules/template.js'

const anonymizationStore = useAnonymizationStore(pinia)
const navigationStore = useNavigationStore(pinia)
const reportStore = useReportStore(pinia)
const templateStore = useTemplateStore(pinia)

export {
	// generic
	anonymizationStore,
	navigationStore,
	reportStore,
	templateStore,
}
