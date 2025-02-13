/* eslint-disable @typescript-eslint/no-explicit-any */
import { FileEntity } from './file'
import { mockFileData } from './file.mock'

describe('File Entity', () => {
	it('should create a File entity with full data', () => {
		const file = new FileEntity(mockFileData()[0])

		expect(file).toBeInstanceOf(FileEntity)
		expect(file).toEqual(mockFileData()[0])
		expect(file.validate().success).toBe(true)
	})

	it('should create a File entity with partial data', () => {
		const file = new FileEntity(mockFileData()[0])

		expect(file).toBeInstanceOf(FileEntity)
		expect(file.id).toBe('')
		expect(file.name).toBe(mockFileData()[0].name)
		expect(file.path).toBe(mockFileData()[0].path)
		expect(file.type).toBe(mockFileData()[0].type)
		expect(file.size).toBe(mockFileData()[0].size)
		expect(file.hash).toBe(mockFileData()[0].hash)
		expect(file.updated).toBe(mockFileData()[0].updated)
		expect(file.created).toBe(mockFileData()[0].created)
		expect(file.locked).toBe(null)
		expect(file.owner).toBe('')
		expect(file.validate().success).toBe(true)
	})

	it('should handle locked array and owner string', () => {
		const mockData = mockFileData()[0]
		mockData.locked = ['token1', 'token2']
		mockData.owner = 'user1'
		const file = new FileEntity(mockData)

		expect(file.locked).toEqual(['token1', 'token2'])
		expect(file.owner).toBe('user1')
		expect(file.validate().success).toBe(true)
	})

	it('should fail validation with invalid data', () => {
		const file = new FileEntity(mockFileData()[1])

		expect(file).toBeInstanceOf(FileEntity)
		expect(file.validate().success).toBe(false)
		expect(file.validate().error?.issues).toContainEqual(expect.objectContaining({
			path: ['id'],
			message: 'String must contain at least 1 character(s)',
		}))
	})
})
