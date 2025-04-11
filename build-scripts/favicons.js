import fs from 'node:fs'
import favicons from 'favicons'
import { ensureDirectoryExistence, log } from './utils.js'

const sourcePath = 'puck/Resources/Public/Icons/Favicons/'
const sourceFileNames = []
const distPath = ensureDirectoryExistence('puck/Resources/Public/Icons/Favicons/packages/')

log.header('Building Favicons')
log.info(`Source directory: ${sourcePath}`)
log.info(`Output directory: ${distPath}`)

fs.readdirSync(sourcePath).forEach(file => {
	if (!file.includes('.svg') && !file.includes('.png')) {
		return
	}

	const fileName = file.replace('.svg', '').replace('.png', '')
	const path = ensureDirectoryExistence(`${distPath}${fileName}/`)
	log.info(`Building package from file: ${fileName}.svg`)

	favicons(`${sourcePath}${file}`, {
		path,
		lang: 'de-DE',
		start_url: '/',
		icons: {
			android: true,
			appleIcon: true,
			appleStartup: false,
			favicons: true,
			windows: false,
			yandex: false,
		},
	}).then(response => {
		for (const file of response.images.concat(response.files)) {
			fs.writeFile(`${path + file.name}`, file.contents, () => {})
		}
		log.success(`Favicon Package Build "${fileName}" Build successful`)
	}).catch(error => {
		log.error(error)
	})
})
