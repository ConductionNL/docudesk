import { SafeParseReturnType, z } from 'zod'
import { TFile } from './file.types'

/**
 * Entity class representing a File with validation
 */
export class FileEntity implements TFile {

	public id: string
	public name: string
	public path: string
	public type: string
	public size: number
	public hash: string
	public updated: string
	public created: string
	public locked: string[] | null // Array of lock tokens or null if not locked
	public owner: string // Owner of the file

	constructor(file: TFile) {
		this.id = file.id || ''
		this.name = file.name
		this.path = file.path
		this.type = file.type
		this.size = file.size
		this.hash = file.hash
		this.updated = file.updated || ''
		this.created = file.created || ''
		this.locked = file.locked || null
		this.owner = file.owner || ''
	}

	/**
	 * Validates the file against a schema
	 * @return {SafeParseReturnType<TFile, unknown>} Object containing validation result with success/error status
	 */
	public validate(): SafeParseReturnType<TFile, unknown> {
		const schema = z.object({
			id: z.string().min(1),
			name: z.string().min(1),
			path: z.string().min(1),
			type: z.string().min(1),
			size: z.number().min(0),
			hash: z.string(),
			updated: z.string(),
			created: z.string(),
			locked: z.array(z.string()).nullable(),
			owner: z.string(),
		})

		return schema.safeParse(this)
	}

}
