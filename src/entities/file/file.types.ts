/**
 * Type definition for File data
 * Represents the structure of a file entity with metadata and access control
 */
export type TFile = {
    id?: string // Unique identifier
    name: string // Name of the file
    path: string // File system path
    type: string // File MIME type
    size: number // File size in bytes
    hash: string // File hash for integrity
    updated: string // Last update timestamp
    created: string // Creation timestamp
    locked: string[] | null // Array of lock tokens or null if not locked
    owner: string // Owner of the file
}

