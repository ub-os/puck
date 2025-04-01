import fs from 'node:fs'
import path from 'node:path'
import chalk from 'chalk'

function ensureDirectoryExistence(filePath) {
	const isDirectory = !path.extname(filePath) || filePath.endsWith('/') || filePath.endsWith('\\')
	const dirPath = isDirectory ? filePath : path.dirname(filePath)
	if (!fs.existsSync(dirPath)) {
		fs.mkdirSync(dirPath, { recursive: true })
	}
	return filePath
}

const log = {
	info: (msg) => console.log(chalk.blue(`ℹ ${msg}`)),
	success: (msg) => console.log(chalk.green(`✓ ${msg}`)),
	warning: (msg) => console.log(chalk.yellow(`⚠ ${msg}`)),
	error: (msg) => console.log(chalk.red(`✗ ${msg}`)),
	header: (msg) => console.log(chalk.bold.cyan(`\n=== ${msg} ===`))
};

export { ensureDirectoryExistence, log }
