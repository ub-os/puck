import esbuild from 'esbuild'
import { ensureDirectoryExistence } from './utils.js'

esbuild.build({
	entryPoints: ['./puck/Resources/Private/JavaScript/puck.js', './puck/Resources/Private/JavaScript/puck-body.js'],
	bundle: true,
	minify: true,
	target: ['es2020'],
	jsx: 'transform',
	outdir: ensureDirectoryExistence('./puck/Resources/Public/JavaScript/dist/'),
	alias: {
		'~': './puck/Resources/Private/JavaScript/',
	},
})
