import { stim } from '@amdeu/stim'
import htmx from 'htmx.org'
import 'htmx.org/dist/ext/hx-head.js'
import 'htmx.org/dist/ext/hx-preload.js'
import 'htmx.org/dist/ext/hx-history-cache.js'
import Accordion from '#/Controller/Accordion.js'
import AnchorHandler from '#/Controller/AnchorHandler.js'
import Carousel from '#/Controller/Carousel.jsx'
import Dialog from '#/Controller/Dialog.js'
import MediaPlayer from '#/Controller/MediaPlayer.jsx'
import Modal from '#/Controller/Modal.js'
import PageHeader from '#/Controller/PageHeader.js'
import ScrollbarWidth from '#/Controller/ScrollbarWidth.jsx'
import ScrollSensitive from '#/Controller/ScrollSensitive.js'
import Showable from '#/Controller/Showable.js'

window.htmx = htmx
Object.assign(htmx.config, {
	defaultSwap: 'outerHTML',
	// since htmx 2.0.5, htmx history restores also transition, which we don't want because it will look glitchy if the restoration involves scrolling
	// instead we add the transition:true modifier to the page-root swap attribute
	// extensions: 'preload',
	// metaCharacter: '.',
})


window.stim = stim
stim.registerController({
	AnchorHandler,
	ScrollbarWidth,
	Showable,
	Accordion,
	Modal,
	Dialog,
	Carousel,
	PageHeader,
	MediaPlayer,
	ScrollSensitive,
})

stim.registerSelectorCallback({
	'.l-row': el => {
		if (el.children.length < 3) return
		el.setAttribute('role', 'list')
		el.childNodes.forEach(child => {
			if (child.nodeType !== 1) return
			child.setAttribute('role', 'listitem')
		})
	},
	'template[data-append-on-load]': el => {
		window.requestAnimationFrame(() => {
			el.parentNode.insertBefore(el.content.cloneNode(true), el)
			htmx.process(el.parentNode)
			el.removeAttribute('data-append-on-load')
		})
	},
	'a.download, a[download]': el => {
		el.setAttribute('data-hx-boost', 'false')
		htmx.process(el)
	}
})

export { htmx, stim }
