import { FileEntity } from './file'
import { TFile } from './file.types'

/**
 * Mock data generator for File entities
 * @returns Array of mock file data
 */
export const mockFileData = (): TFile[] => [
	{
		id: '1234a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		name: 'test-file.txt',
		path: '/files/test-file.txt',
		type: 'text/plain',
		size: 1024,
		hash: 'abc123def456',
		created: new Date().toISOString(),
		updated: new Date().toISOString(),
		locked: ['token1', 'token2'], // Array of lock tokens
		owner: 'user1', // Owner of the file
	},
	{
		id: '5678a1e5-b54d-43ad-abd1-4b5bff5fcd3f', 
		name: 'image.jpg',
		path: '/files/image.jpg',
		type: 'image/jpeg',
		size: 2048576,
		hash: 'xyz789uvw321',
		created: new Date().toISOString(),
		updated: new Date().toISOString(),
		locked: null, // Not locked
		owner: 'user2', // Owner of the file
	},
]

/**
 * Creates FileEntity instances from mock data
 * @param data Optional array of mock file data to use instead of default
 * @returns Array of FileEntity instances
 */
export const mockFile = (data: TFile[] = mockFileData()): TFile[] => data.map(item => new FileEntity(item))
