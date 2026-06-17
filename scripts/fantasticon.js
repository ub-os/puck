import fs from 'node:fs'
import { generateFonts } from 'fantasticon'
import { ensureDirectoryExistence, log } from './utils.js'

const inputDir = 'src/icons/'
const outputDir = ensureDirectoryExistence('Resources/Public/Fonts/Icons/')

log.header('Building Icon Font')
log.info(`Source directory: ${inputDir}`)
log.info(`Output directory: ${outputDir}`)

generateFonts({
	inputDir,
	outputDir,
	assetTypes: ['scss', 'json', 'html', 'css'],
	fontTypes: ['ttf', 'woff', 'woff2'],
	fontsUrl: '../../Fonts/Icons',
	prefix: 'icon-',
	tag: '*',
	formatOptions: {
		svg: {
			normalize: true,
			fontHeight: 1000,
			descent: 40,
			centervertically: true,
			centerhorizontally: true,
		},
	},
	templates: {
		scss: 'scripts/fantasticon/scss.hbs',
	},
	pathOptions: {
		scss: 'src/sass/02-generic/_icon-font.scss',
	},
	codepoints: {},
	getIconId: ({
		basename, // `string` - Example: 'foo';
	}) => basename.toLowerCase(),
}).then(results => {
	// create js file with codepoints
	const jsString = `export default ${JSON.stringify(results.codepoints)};`
	fs.writeFileSync(`${outputDir}icons.js`, jsString)
	log.success('Icon Font Build successful')
}).catch(error => {
	log.error(error)
	process.exit(1)
})
