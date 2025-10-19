import esbuild from 'esbuild'
import { ensureDirectoryExistence, log } from './utils.js'
import fs from 'fs'

const distPath = ensureDirectoryExistence('./puck/Resources/Public/JavaScript/dist/')
const filePaths = ['./puck/Resources/Private/JavaScript/puck.js']

log.header('Building JavaScript')
log.info(`Source files: ${filePaths.join(', ')}`)
log.info(`Output directory: ${distPath}`)
Promise.all([
	buildFiles(filePaths, distPath, false),
	buildFiles(filePaths, distPath, true)
]).then(() => {
	log.success('JavaScript Build successful')
}).catch((error) => {
	log.error(error)
	process.exit(1)
})

async function buildFiles(entryPoints, outdir, minify = false) {
	const result = await esbuild.build({
		entryPoints,
		outdir,
		minify,
		bundle: true,
		target: ['es2020'],
		metafile: true,
		jsx: 'transform',
		jsxFactory: 'jsx',
		sourcemap: !minify,
		outExtension: {
			'.js': minify ? '.min.js' : '.js',
		},
		alias: {
			'~': './puck/Resources/Private/JavaScript/',
		},
	})
	fs.writeFileSync(`${outdir}/meta${minify ? '.min' : ''}.json`, JSON.stringify(result.metafile, null, 2))
}