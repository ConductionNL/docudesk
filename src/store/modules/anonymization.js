/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Anonymization } from '../../entities/index.js'

/**
 * Store for managing anonymization logs
 * 
 * @see website/docs/api/anonymization-logs.md for documentation
 * 
 * @package DocuDesk
 * @author Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (c) 2024 Conduction B.V.
 * @license EUPL-1.2
 * @version 1.0.0
 */
export const useAnonymizationStore = defineStore('anonymization', {
    state: () => ({
        anonymizationItem: false,
        anonymizationList: [],
    }),
    actions: {
        /**
         * Sets the active anonymization item
         * 
         * @param {Object|null} anonymizationItem - Anonymization item data
         */
        setAnonymizationItem(anonymizationItem) {
            this.anonymizationItem = anonymizationItem && new Anonymization(anonymizationItem)
            console.log('Active anonymization item set to ' + (anonymizationItem ? anonymizationItem.id : 'null'))
        },
        
        /**
         * Sets the list of anonymization items
         * 
         * @param {Array} anonymizationList - List of anonymization items
         */
        setAnonymizationList(anonymizationList) {
            this.anonymizationList = anonymizationList.map(
                (anonymizationItem) => new Anonymization(anonymizationItem),
            )
            console.log('Anonymization list set to ' + anonymizationList.length + ' items')
        },
        
        /**
         * Refreshes the list of anonymization items
         * 
         * @param {string|null} search - Search query
         * @param {Object} filters - Filter parameters
         * @returns {Promise<Object>} Response and data
         * 
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedMethod
         */
        /* istanbul ignore next */ // ignore this for Jest until moved into a service
        async refreshAnonymizationList(search = null, filters = {}) {
            let endpoint = '/index.php/apps/docudesk/api/objects/anonymization'
            
            // Add search parameter if provided
            const params = new URLSearchParams()
            if (search !== null && search !== '') {
                params.append('_search', search)
            }
            
            // Add filters
            for (const [key, value] of Object.entries(filters)) {
                if (value !== null && value !== '') {
                    params.append(key, value)
                }
            }
            
            // Append params to endpoint if any exist
            const queryString = params.toString()
            if (queryString) {
                endpoint += '?' + queryString
            }
            
            const response = await fetch(endpoint, {
                method: 'GET',
            })

            const data = (await response.json()).results

            this.setAnonymizationList(data)

            return { response, data }
        },
        
        /**
         * Gets a specific anonymization item by ID
         * 
         * @param {string} id - Anonymization item ID
         * @returns {Promise<Object>} Anonymization item data
         * 
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedMethod
         */
        async getAnonymization(id) {
            const endpoint = `/index.php/apps/docudesk/api/objects/anonymization/${id}`
            try {
                const response = await fetch(endpoint, {
                    method: 'GET',
                })
                const data = await response.json()
                this.setAnonymizationItem(data)
                return data
            } catch (err) {
                console.error(err)
                throw err
            }
        },
        
        /**
         * Deletes an anonymization item
         * 
         * @param {Object} anonymizationItem - Anonymization item to delete
         * @returns {Promise<Object>} Response and data
         * 
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedMethod
         */
        async deleteAnonymization(anonymizationItem) {
            if (!anonymizationItem.id) {
                throw new Error('No anonymization item to delete')
            }

            console.log('Deleting anonymization...')

            const endpoint = `/index.php/apps/docudesk/api/objects/anonymization/${anonymizationItem.id}`

            try {
                const response = await fetch(endpoint, {
                    method: 'DELETE',
                })

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`)
                }

                const responseData = await response.json()

                if (!responseData || typeof responseData !== 'object') {
                    throw new Error('Invalid response data')
                }

                this.refreshAnonymizationList()
                this.setAnonymizationItem(null)

                return { response, data: responseData }
            } catch (error) {
                console.error('Error deleting anonymization:', error)
                throw new Error(`Failed to delete anonymization: ${error.message}`)
            }
        },
        
        /**
         * Saves an anonymization item
         * 
         * @param {Object} anonymizationItem - Anonymization item to save
         * @returns {Promise<Object>} Response and data
         * 
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedMethod
         */
        async saveAnonymization(anonymizationItem) {
            if (!anonymizationItem) {
                throw new Error('No anonymization item to save')
            }

            console.log('Saving anonymization...')

            const isNewAnonymization = !anonymizationItem.id
            const endpoint = isNewAnonymization
                ? '/index.php/apps/docudesk/api/objects/anonymization'
                : `/index.php/apps/docudesk/api/objects/anonymization/${anonymizationItem.id}`
            const method = isNewAnonymization ? 'POST' : 'PUT'

            // change updated to current date as a singular iso date string
            anonymizationItem.updated = new Date().toISOString()

            try {
                const response = await fetch(
                    endpoint,
                    {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(anonymizationItem),
                    },
                )

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`)
                }

                const responseData = await response.json()

                if (!responseData || typeof responseData !== 'object') {
                    throw new Error('Invalid response data')
                }

                const data = new Anonymization(responseData)

                this.setAnonymizationItem(data)
                this.refreshAnonymizationList()

                return { response, data }
            } catch (error) {
                console.error('Error saving anonymization:', error)
                throw new Error(`Failed to save anonymization: ${error.message}`)
            }
        },
        
        /**
         * Anonymizes a document
         * 
         * @param {string} nodeId - Nextcloud node ID of the document to anonymize
         * @param {string} fileName - Name of the document
         * @param {number} confidenceThreshold - Confidence threshold for entity detection
         * @returns {Promise<Object>} Response and data
         * 
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedMethod
         */
        async anonymizeDocument(nodeId, fileName, confidenceThreshold = 0.7) {
            if (!nodeId) {
                throw new Error('No node ID provided')
            }

            console.log('Anonymizing document...')

            const endpoint = '/index.php/apps/docudesk/api/anonymize'
            
            try {
                const response = await fetch(
                    endpoint,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            nodeId,
                            fileName,
                            confidenceThreshold
                        }),
                    },
                )

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`)
                }

                const responseData = await response.json()

                if (!responseData || typeof responseData !== 'object') {
                    throw new Error('Invalid response data')
                }

                // Refresh the anonymization list to include the new anonymization
                this.refreshAnonymizationList()

                return { response, data: responseData }
            } catch (error) {
                console.error('Error anonymizing document:', error)
                throw new Error(`Failed to anonymize document: ${error.message}`)
            }
        },
        
        /**
         * De-anonymizes a document
         * 
         * @param {string} anonymizedNodeId - Nextcloud node ID of the anonymized document
         * @param {string} anonymizationKey - Key used to de-anonymize the document
         * @returns {Promise<Object>} Response and data
         * 
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedMethod
         */
        async deanonymizeDocument(anonymizedNodeId, anonymizationKey) {
            if (!anonymizedNodeId || !anonymizationKey) {
                throw new Error('Missing required parameters')
            }

            console.log('De-anonymizing document...')

            const endpoint = '/index.php/apps/docudesk/api/deanonymize'
            
            try {
                const response = await fetch(
                    endpoint,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            anonymizedNodeId,
                            anonymizationKey
                        }),
                    },
                )

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`)
                }

                const responseData = await response.json()

                if (!responseData || typeof responseData !== 'object') {
                    throw new Error('Invalid response data')
                }

                return { response, data: responseData }
            } catch (error) {
                console.error('Error de-anonymizing document:', error)
                throw new Error(`Failed to de-anonymize document: ${error.message}`)
            }
        }
    },
}) 