import esbuild from 'esbuild'
import fs from 'fs'
import { ensureDirectoryExistence, log } from './utils.js'

const srcPaths = ['./src/js/puck.js']
const distPath = ensureDirectoryExistence('./Resources/Public/JavaScript/dist/')

log.header('Building JavaScript')
log.info(`Source files: ${srcPaths.join(', ')}`)
log.info(`Output directory: ${distPath}`)
Promise.all([
	buildFiles(srcPaths, distPath, false),
	buildFiles(srcPaths, distPath, true)
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
		target: ['es2022'],
		metafile: true,
		jsx: 'transform',
		jsxFactory: 'jsx',
		sourcemap: !minify,
		outExtension: {
			'.js': minify ? '.min.js' : '.js',
		},
	})
	fs.writeFileSync(`${outdir}/meta${minify ? '.min' : ''}.json`, JSON.stringify(result.metafile, null, 2))
}