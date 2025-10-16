import { stim } from '@oliveoilexpert/stim'
import Accordion from '~/Controller/Accordion'
import AnchorHandler from '~/Controller/AnchorHandler'
import Carousel from '~/Controller/Carousel.jsx'
import Dialog from '~/Controller/Dialog'
import MediaPlayer from '~/Controller/MediaPlayer.jsx'
import Modal from '~/Controller/Modal'
import PageHeader from '~/Controller/PageHeader'
import ScrollSensitive from '~/Controller/ScrollSensitive'
import ScrollbarWidth from '~/Controller/ScrollbarWidth.jsx'
import Showable from '~/Controller/Showable'

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
			el.removeAttribute('data-append-on-load')
		})
	},
})

export { stim }
