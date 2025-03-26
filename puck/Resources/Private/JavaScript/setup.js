import { stim } from '~/stim2'
import htmx from 'htmx.org/dist/htmx.cjs.js'
import Accordion from '~/stim2/Accordion'
import AnchorScrolling from '~/stim2/AnchorScrolling'
import Carousel from '~/stim2/Carousel'
import Dialog from '~/stim2/Dialog'
import MediaPlayer from '~/stim2/MediaPlayer'
import Modal from '~/stim2/Modal'
import PageHeader from '~/stim2/PageHeader'
import ScrollSensitive from '~/stim2/ScrollSensitive'
import ScrollbarWidth from '~/stim2/ScrollbarWidth'
import Showable from '~/stim2/Showable'
import Root from '~/stim2/Root'
import { noDragClick } from '~/Utility/DomUtility'

window.htmx = htmx
Object.assign(htmx.config, {
	scrollBehavior: 'auto',
	defaultSwapStyle: 'outerHTML',
	defaultSwapDelay: 0,
	defaultSettleDelay: 0,
	globalViewTransitions: true,
	allowScriptTags: true,
	allowEval: false,
	refreshOnHistoryMiss: true,
})

window.stim = stim
// Object.assign(stim.config, {
// 	observeAttributes: false,
// 	observeAspectAttributes: false,
// 	customElementPrefix: 'pk-',
// })

stim.registerTrait({
	Root,
	AnchorScrolling,
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

//stim.registerCustomElement(['carousel', 'media-player', 'root'])
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