/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useFileStore } from './file.js'
import { FileEntity, mockFile } from '../../entities/index.js'

describe('File Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets file item correctly', () => {
		const store = useFileStore()

		store.setFileItem(mockFile()[0])

		expect(store.fileItem).toBeInstanceOf(FileEntity)
		expect(store.fileItem).toEqual(mockFile()[0])

		expect(store.fileItem.validate().success).toBe(true)
	})

	it('sets file list correctly', () => {
		const store = useFileStore()

		store.setFiles(mockFile())

		expect(store.files).toHaveLength(mockFile().length)

		store.files.forEach((item, index) => {
			expect(item).toBeInstanceOf(FileEntity)
			expect(item).toEqual(mockFile()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
