import fs from 'node:fs'
import path from 'node:path'

function ensureDirectoryExistence(filePath) {
	const isDirectory = !path.extname(filePath) || filePath.endsWith('/') || filePath.endsWith('\\')
	const dirPath = isDirectory ? filePath : path.dirname(filePath)
	if (!fs.existsSync(dirPath)) {
		fs.mkdirSync(dirPath, { recursive: true })
	}
	return filePath
}

export { ensureDirectoryExistence }
