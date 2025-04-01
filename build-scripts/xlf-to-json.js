import fs from 'node:fs'
import path from 'node:path'
import xliff from 'xliff'

const extensions = ['puck']
function ensureDirectoryExistence(filePath) {
	const dirname = path.dirname(filePath)
	if (fs.existsSync(dirname)) {
		return true
	}
	ensureDirectoryExistence(dirname)
	fs.mkdirSync(dirname)
}
function writeJson(xlf, jsonPath) {
	xliff.xliff12ToJs(xlf, (err, res) => {
		if (err) {
			throw err
		}
		ensureDirectoryExistence(jsonPath)
		fs.writeFileSync(jsonPath, JSON.stringify(res))
	})
}
for (const ext of extensions) {
	const xlfPath = `${ext}/Resources/Private/Language/`
	const jsonPath = `${ext}/Resources/Public/json/language/`
	const xlfFiles = fs.readdirSync(xlfPath)
	const xlfData = []
	for (const file of xlfFiles) {
		if (fs.lstatSync(xlfPath + file).isFile()) {
			writeJson(
				fs.readFileSync(xlfPath + file, 'utf8'),
				`${jsonPath + file}.json`,
			)
		}
	}
}
