import esbuild from 'esbuild'
import { ensureDirectoryExistence } from './utils.js'

const distPath = ensureDirectoryExistence('./puck/Resources/Public/JavaScript/dist/')
const filePaths = ['./puck/Resources/Private/JavaScript/puck.js', './puck/Resources/Private/JavaScript/puck-body.js']
function buildFiles(entryPoints, outdir, minify = false) {
	esbuild.build({
		entryPoints,
		outdir,
		minify,
		bundle: true,
		target: ['es2020'],
		jsx: 'transform',
		sourcemap: true,
		outExtension: {
			'.js': minify ? '.min.js' : '.js',
		},
		alias: {
			'~': './puck/Resources/Private/JavaScript/',
		},
	})
}
buildFiles(filePaths, distPath, false)
buildFiles(filePaths, distPath, true)
