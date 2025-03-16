/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showSuccess, showError } from '@nextcloud/dialogs'

document.addEventListener('DOMContentLoaded', () => {
    // Get elements
    const saveButton = document.getElementById('docudesk-save-settings')
    const statusElement = document.getElementById('docudesk-settings-status')
    const presidioAnalyzerUrlInput = document.getElementById('docudesk-presidio-analyzer-url')
    const presidioAnonymizerUrlInput = document.getElementById('docudesk-presidio-anonymizer-url')
    const confidenceThresholdInput = document.getElementById('docudesk-confidence-threshold')
    const enableReportingCheckbox = document.getElementById('docudesk-enable-reporting')
    const enableAnonymizationCheckbox = document.getElementById('docudesk-enable-anonymization')
    const storeOriginalTextCheckbox = document.getElementById('docudesk-store-original-text')

    if (!saveButton) {
        console.error('DocuDesk settings elements not found')
        return
    }

    // Save settings handler
    saveButton.addEventListener('click', async () => {
        // Get values
        const presidioAnalyzerUrl = presidioAnalyzerUrlInput.value.trim()
        const presidioAnonymizerUrl = presidioAnonymizerUrlInput.value.trim()
        const confidenceThreshold = parseFloat(confidenceThresholdInput.value)
        const enableReporting = enableReportingCheckbox.checked
        const enableAnonymization = enableAnonymizationCheckbox.checked
        const storeOriginalText = storeOriginalTextCheckbox.checked

        // Validate values
        if (!presidioAnalyzerUrl) {
            showError(t('docudesk', 'Presidio Analyzer URL cannot be empty'))
            return
        }

        if (!presidioAnonymizerUrl) {
            showError(t('docudesk', 'Presidio Anonymizer URL cannot be empty'))
            return
        }

        if (isNaN(confidenceThreshold) || confidenceThreshold < 0 || confidenceThreshold > 1) {
            showError(t('docudesk', 'Confidence threshold must be a number between 0 and 1'))
            return
        }

        // Show loading state
        saveButton.disabled = true
        statusElement.textContent = t('docudesk', 'Saving settings...')

        try {
            // Save settings via API
            await axios.post(generateUrl('/apps/docudesk/settings'), {
                presidioAnalyzerUrl,
                presidioAnonymizerUrl,
                confidenceThreshold,
                enableReporting,
                enableAnonymization,
                storeOriginalText
            })

            // Show success
            statusElement.textContent = ''
            showSuccess(t('docudesk', 'Settings saved successfully'))
        } catch (error) {
            console.error('Error saving DocuDesk settings:', error)
            statusElement.textContent = ''
            showError(t('docudesk', 'Failed to save settings: {error}', { error: error.message }))
        } finally {
            saveButton.disabled = false
        }
    })

    // Test Analyzer connection button
    const testAnalyzerButton = document.createElement('button')
    testAnalyzerButton.textContent = t('docudesk', 'Test Analyzer')
    testAnalyzerButton.classList.add('docudesk-test-button')
    testAnalyzerButton.addEventListener('click', async () => {
        const presidioUrl = presidioAnalyzerUrlInput.value.trim()
        
        if (!presidioUrl) {
            showError(t('docudesk', 'Please enter a Presidio Analyzer URL first'))
            return
        }

        testAnalyzerButton.disabled = true
        testAnalyzerButton.textContent = t('docudesk', 'Testing...')

        try {
            await axios.post(generateUrl('/apps/docudesk/settings/test-presidio-analyzer'), {
                presidioUrl,
            })
            showSuccess(t('docudesk', 'Analyzer connection successful'))
        } catch (error) {
            console.error('Error testing Presidio Analyzer connection:', error)
            showError(t('docudesk', 'Analyzer connection failed: {error}', { error: error.message }))
        } finally {
            testAnalyzerButton.disabled = false
            testAnalyzerButton.textContent = t('docudesk', 'Test Analyzer')
        }
    })

    // Test Anonymizer connection button
    const testAnonymizerButton = document.createElement('button')
    testAnonymizerButton.textContent = t('docudesk', 'Test Anonymizer')
    testAnonymizerButton.classList.add('docudesk-test-button')
    testAnonymizerButton.addEventListener('click', async () => {
        const presidioUrl = presidioAnonymizerUrlInput.value.trim()
        
        if (!presidioUrl) {
            showError(t('docudesk', 'Please enter a Presidio Anonymizer URL first'))
            return
        }

        testAnonymizerButton.disabled = true
        testAnonymizerButton.textContent = t('docudesk', 'Testing...')

        try {
            await axios.post(generateUrl('/apps/docudesk/settings/test-presidio-anonymizer'), {
                presidioUrl,
            })
            showSuccess(t('docudesk', 'Anonymizer connection successful'))
        } catch (error) {
            console.error('Error testing Presidio Anonymizer connection:', error)
            showError(t('docudesk', 'Anonymizer connection failed: {error}', { error: error.message }))
        } finally {
            testAnonymizerButton.disabled = false
            testAnonymizerButton.textContent = t('docudesk', 'Test Anonymizer')
        }
    })

    // Add test buttons after the respective URL inputs
    const analyzerSetting = presidioAnalyzerUrlInput.closest('.docudesk-setting')
    if (analyzerSetting) {
        analyzerSetting.appendChild(testAnalyzerButton)
    }

    const anonymizerSetting = presidioAnonymizerUrlInput.closest('.docudesk-setting')
    if (anonymizerSetting) {
        anonymizerSetting.appendChild(testAnonymizerButton)
    }

    // Toggle dependency: disable storeOriginalText if enableAnonymization is unchecked
    enableAnonymizationCheckbox.addEventListener('change', () => {
        storeOriginalTextCheckbox.disabled = !enableAnonymizationCheckbox.checked
        if (!enableAnonymizationCheckbox.checked) {
            storeOriginalTextCheckbox.checked = false
        }
    })

    // Initialize dependency state
    storeOriginalTextCheckbox.disabled = !enableAnonymizationCheckbox.checked
}) 