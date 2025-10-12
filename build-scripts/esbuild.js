import esbuild from 'esbuild'
import { ensureDirectoryExistence, log } from './utils.js'

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
	await esbuild.build({
		entryPoints,
		outdir,
		minify,
		bundle: true,
		target: ['es2020'],
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
}