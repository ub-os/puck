import fs from 'node:fs'
import path from 'node:path'

function ensureDirectoryExistence(path) {
	const dirname = path.dirname(path)
	if (!fs.existsSync(dirname)) {
		fs.mkdirSync(dirname, { recursive: true })
	}
	return path
}

export { ensureDirectoryExistence }