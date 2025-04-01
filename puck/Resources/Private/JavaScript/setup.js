import { stim } from '@oliveoilexpert/stim'
import htmx from 'htmx.org/dist/htmx.cjs.js'
import { noDragClick } from '~/Utility/DomUtility'
import Accordion from '~/Controller/Accordion'
import AnchorHandler from '~/Controller/AnchorHandler'
import Carousel from '~/Controller/Carousel'
import Dialog from '~/Controller/Dialog'
import MediaPlayer from '~/Controller/MediaPlayer'
import Modal from '~/Controller/Modal'
import PageHeader from '~/Controller/PageHeader'
import ScrollSensitive from '~/Controller/ScrollSensitive'
import ScrollbarWidth from '~/Controller/ScrollbarWidth'
import Showable from '~/Controller/Showable'

window.htmx = htmx
Object.assign(htmx.config, {
	scrollBehavior: 'auto',
	defaultSwapStyle: 'outerHTML',
	defaultSwapDelay: 0,
	defaultSettleDelay: 0,
	globalViewTransitions: true,
	allowScriptTags: true,
	allowEval: false,
	//refreshOnHistoryMiss: true,
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
	'[data-link-area]': el => {
		el.style.cursor = 'pointer'
		noDragClick(el, e => {
			if (e.button > 1 || e.target.tagName === 'A') return
			const link = el.querySelector('a')
			if (e.metaKey || e.button == 1) {
				window.open(link.href, '_blank')
				return
			}
			link.click()
		})
	},
})

export { stim, htmx }