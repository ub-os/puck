import esbuild from 'esbuild'
import aliasPlugin from 'esbuild-plugin-alias'
import { ensureDirectoryExistence } from './utils'

esbuild.build({
	entryPoints: ['./puck/Resources/Private/JavaScript/puck.js', './puck/Resources/Private/JavaScript/puck-body.js'],
	bundle: true,
	minify: true,
	target: ['es2020'], // Modern browsers
	jsxFactory: 'jsx',
	outdir: ensureDirectoryExistence('./puck/Resources/Public/JavaScript/dist/'),
	plugins: [
		aliasPlugin({
			'~': './puck/Resources/Private/JavaScript'
		})
	]
})