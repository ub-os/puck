import fs from 'node:fs'
import favicons from 'favicons'
import { ensureDirectoryExistence, log } from './utils.js'

const srcPath = 'src/favicons/'
const distPath = ensureDirectoryExistence('Resources/Public/Favicons/packages/')

log.header('Building Favicons')
log.info(`Source directory: ${srcPath}`)
log.info(`Output directory: ${distPath}`)

// Output is kept to what \UBOS\Puck\Frontend\PageAssetDecorator actually links:
// favicon.ico, icon.svg (copied from source), apple-touch-icon.png,
// manifest.webmanifest + the two android-chrome PNGs it references.
// The array form of an `icons` platform option is a filter by output filename
// (favicons >= 7).
fs.readdirSync(srcPath).forEach(file => {
	if (!file.endsWith('.svg') && !file.endsWith('.png')) {
		return
	}

	const fileName = file.replace(/\.(svg|png)$/, '')
	const outDir = `${distPath}${fileName}/`
	fs.rmSync(outDir, { recursive: true, force: true }) // drop stale variants from previous builds
	ensureDirectoryExistence(outDir)
	log.info(`Building package from file: ${file}`)

	favicons(`${srcPath}${file}`, {
		lang: 'de-DE',
		start_url: '/',
		manifestRelativePaths: true, // icon URLs resolve against the served manifest
		icons: {
			android: ['android-chrome-192x192.png', 'android-chrome-512x512.png'],
			appleIcon: ['apple-touch-icon.png'], // single 180x180; iOS downscales
			appleStartup: false,
			favicons: ['favicon.ico'], // multi-resolution .ico (16/24/32/48/64)
			windows: false,
			yandex: false,
		},
	}).then(response => {
		for (const asset of [...response.images, ...response.files]) {
			fs.writeFileSync(`${outDir}${asset.name}`, asset.contents)
		}
		// Pass the source SVG through untouched - it is the primary favicon.
		if (file.endsWith('.svg')) {
			fs.copyFileSync(`${srcPath}${file}`, `${outDir}icon.svg`)
		}
		log.success(`Favicon package "${fileName}" built`)
	}).catch(error => {
		log.error(error)
	})
})
