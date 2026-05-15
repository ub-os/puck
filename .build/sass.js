import { Buffer } from 'node:buffer'
import fs from 'node:fs'
import { globSync } from 'glob'
import * as sass from 'sass'
import { ensureDirectoryExistence, log } from './utils.js'

const sourcePath = 'Resources/Private/Stylesheets/'
const fileNames = ['global-layer-order', 'puck', 'puck-backend']
const distPath = ensureDirectoryExistence('Resources/Public/Css/dist/')

log.header('Building CSS from SASS')
log.info(`Source files: ${fileNames.map(name => sourcePath + name).join(', ')}`)
log.info(`Output directory: ${distPath}`)

const globImporter = (path, transformFunction = ({ contents }) => contents) => {
	let cleanPath = path
	if (!path.endsWith('/')) {
		cleanPath = `${path}/`
	}
	if (path.startsWith('./')) {
		cleanPath = path.substring(2)
	}
	return {
		canonicalize(url) {
			if (!url.includes('*')) return null
			if (!url.includes(':')) return new URL(`x:${url}`)
			return new URL(url)
		},
		load(canonicalUrl) {
			let contents = ''
			if (canonicalUrl.pathname.startsWith('/')) {
				canonicalUrl.pathname = canonicalUrl.pathname.substring(1)
			}
			const depth = (canonicalUrl.pathname.match(/\//g) || []).length
			const files = new Set()
			globSync(cleanPath + canonicalUrl.pathname).forEach(fullPath => {
				const pathParts = fullPath.split('/')
				const importPath = pathParts.slice(pathParts.length - 1 - depth, pathParts.length).join('/')
				const file = {
					name: pathParts[pathParts.length - 1],
					importPath,
					fullPath,
				}
				files.add(file)
				contents += `@use "${importPath}";\n`
			})
			contents = transformFunction({ contents, canonicalUrl, files })
			return {
				contents,
				syntax: 'scss',
			}
		},
	}
}

function globImporterTransformer({ contents, canonicalUrl, files }) {
	const getNamespaceFromFile = file => {
		let namespace = file.name.split('.')[0]
		if (namespace.startsWith('_')) {
			namespace = namespace.substring(1)
		}
		return namespace
	}
	const fileHasMainMixin = file => {
		const namespace = getNamespaceFromFile(file)
		const fileContents = fs.readFileSync(file.fullPath, 'utf8')
		if (!fileContents.includes(`@mixin ${namespace}`) && !fileContents.includes(`=${namespace}`)) return false
		return namespace
	}

	if (canonicalUrl.protocol === 'include-mixins-in-namespace-class:') {
		files.forEach(file => {
			const mixinName = fileHasMainMixin(file)
			if (!mixinName) return
			contents += `.${mixinName} { @include ${mixinName}.${mixinName}; }\n`
		})
	}
	if (canonicalUrl.protocol === 'include-mixins-at-root:') {
		files.forEach(file => {
			const mixinName = fileHasMainMixin(file)
			if (!mixinName) return
			contents += `@include ${mixinName}.${mixinName};\n`
		})
	}
	return contents
}

for (const fileName of fileNames) {
	renderFile(fileName)
}

function renderFile(fileName) {
	const sassFile = `${sourcePath + fileName}.sass`
	const cssFile = `${distPath + fileName}.css`
	const result = sass.compile(sassFile, {
		importers: [globImporter(sourcePath, globImporterTransformer)],
		loadPaths: [sourcePath, 'node_modules/'],
		quietDeps: true,
		sourceMap: true,
	})
	log.info(`Building ${cssFile}`)
	ensureDirectoryExistence(cssFile)
	result.sourceMap.sources = result.sourceMap.sources.map(source => {
		if (source.startsWith('file://')) {
			const pathParts = source.split('/')
			return pathParts[pathParts.length - 1]
		}
		return source
	})
	const sm = JSON.stringify(result.sourceMap)
	const smBase64 = (Buffer.from(sm, 'utf8') || '').toString('base64')
	const smComment = `/*# sourceMappingURL=data:application/json;charset=utf-8;base64,${smBase64} */`
	const css = result.css.toString() + '\n'.repeat(2) + smComment
	fs.writeFile(cssFile, css, err => {
		if (err) return log.error(err)
		log.success(`CSS Build "${fileName}" successful`)
	})
}
