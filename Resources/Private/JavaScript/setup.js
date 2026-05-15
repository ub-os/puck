import { stim } from '@amdeu/stim'
import htmx from 'htmx.org'
import Accordion from './Controller/Accordion.js'
import AnchorHandler from './Controller/AnchorHandler.js'
import Carousel from './Controller/Carousel.jsx'
import Dialog from './Controller/Dialog.js'
import MediaPlayer from './Controller/MediaPlayer.jsx'
import Modal from './Controller/Modal.js'
import PageHeader from './Controller/PageHeader.js'
import ScrollSensitive from './Controller/ScrollSensitive.js'
import ScrollbarWidth from './Controller/ScrollbarWidth.jsx'
import Showable from './Controller/Showable.js'

window.htmx = htmx
Object.assign(htmx.config, {
	scrollBehavior: 'instant',
	defaultSwapStyle: 'outerHTML',
	defaultSwapDelay: 0,
	defaultSettleDelay: 0,
	// since htmx 2.0.5, htmx history restores also transition with "globalViewTransitions", which we don't want because it will look glitchy if the restoration involves scrolling
	// instead we add the transition:true modifier to the page-root swap attribute
	globalViewTransitions: false,
	allowScriptTags: true,
	allowEval: false,
	// historyCacheSize: 0,
	refreshOnHistoryMiss: true,
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

export { stim, htmx }
