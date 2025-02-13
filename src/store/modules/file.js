/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { FileEntity } from '../../entities/index.js'

/**
 * File store for managing file state and operations
 */
export const useFileStore = defineStore('file', {
	state: () => ({
		fileItem: false, // Single file item
		files: [], // List of files
	}),
	actions: {
		/**
		 * Set active file item
		 * @param {object} fileItem File object to set
		 */
		setFileItem(fileItem) {
			this.fileItem = fileItem && new FileEntity(fileItem)
			console.log('Active file item set to ' + fileItem)
		},

		/**
		 * Set list of files
		 * @param {Array} files Array of file objects
		 */
		setFiles(files) {
			this.files = files.map(
				(fileItem) => new FileEntity(fileItem),
			)
			console.log('File list set to ' + files.length + ' items')
		},

		/**
		 * Refresh list of files, optionally filtered by search
		 * @param {string|null} search Optional search term
		 */
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshFileList(search = null) {
			let endpoint = '/index.php/apps/openregister/api/files'
			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}
			try {
				const response = await fetch(endpoint, {
					method: 'GET',
				})
				const data = await response.json()
				this.setFiles(data.results)
			} catch (err) {
				console.error('Error refreshing file list:', err)
				throw new Error(`Failed to refresh files: ${err.message}`)
			}
		},

		/**
		 * Get a single file by ID
		 * @param {number} id File ID
		 * @returns {Promise} Promise resolving to file data
		 */
		async getFile(id) {
			const endpoint = `/index.php/apps/openregister/api/files/${id}`
			try {
				const response = await fetch(endpoint, {
					method: 'GET',
				})
				const data = await response.json()
				this.setFileItem(data)
				return data
			} catch (err) {
				console.error('Error getting file:', err)
				throw new Error(`Failed to get file: ${err.message}`)
			}
		},

		/**
		 * Delete a file
		 * @param {object} fileItem File object to delete
		 * @returns {Promise} Promise resolving when file is deleted
		 */
		async deleteFile(fileItem) {
			if (!fileItem.id) {
				throw new Error('No file item to delete')
			}

			console.log('Deleting file...')
			const endpoint = `/index.php/apps/openregister/api/files/${fileItem.id}`

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

				this.refreshFileList()
				this.setFileItem(null)

				return { response, data: responseData }
			} catch (error) {
				console.error('Error deleting file:', error)
				throw new Error(`Failed to delete file: ${error.message}`)
			}
		},

		/**
		 * Save or update a file
		 * @param {object} fileItem File object to save
		 * @returns {Promise} Promise resolving to saved file data
		 */
		async saveFile(fileItem) {
			if (!fileItem) {
				throw new Error('No file item to save')
			}

			console.log('Saving file...')

			const isNewFile = !fileItem.id
			const endpoint = isNewFile
				? '/index.php/apps/openregister/api/files'
				: `/index.php/apps/openregister/api/files/${fileItem.id}`
			const method = isNewFile ? 'POST' : 'PUT'

			fileItem.updated = new Date().toISOString()

			try {
				const response = await fetch(
					endpoint,
					{
						method,
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify(fileItem),
					},
				)

				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`)
				}

				const data = new FileEntity(await response.json())

				this.refreshFileList()
				this.setFileItem(data)

				return { response, data }
			} catch (error) {
				console.error('Error saving file:', error)
				throw new Error(`Failed to save file: ${error.message}`)
			}
		},
	},
})
