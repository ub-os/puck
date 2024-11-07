import { stim } from '@oliveoilexpert/stim'
import htmx from 'htmx.org/dist/htmx.cjs.js'
import ScrollbarWidth from "~/Aspects/ScrollbarWidth"
import AnchorScrolling from "~/Aspects/AnchorScrolling"
import Showable from '~/Aspects/Showable'
import Accordion from '~/Aspects/Accordion'
import Dialog from "~/Aspects/Dialog"
import Modal from "~/Aspects/Modal"
import Carousel from '~/Aspects/Carousel'
import PageHeader from "~/Aspects/PageHeader"
import MediaPlayer from "~/Aspects/MediaPlayer"
import ScrollSensitive from '~/Aspects/ScrollSensitive'
import FormPage from '~/Aspects/FormPage'
import Root from "~/Aspects/Root"
import { noDragClick } from "~/Utility/DomUtility"

window.htmx = htmx
Object.assign(htmx.config, {
    scrollBehavior: 'auto',
    defaultSwapStyle: 'outerHTML',
    defaultSwapDelay: 0,
    defaultSettleDelay: 0,
    globalViewTransitions: true,
    allowScriptTags: true,
    allowEval: false,
    refreshOnHistoryMiss: true
})

window.stim = stim
Object.assign(stim.config, {
    observeAttributes: false,
    observeAspectAttributes: false,
    customElementPrefix: 'pk-',
})

stim.registerAspect({
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
    FormPage,
    Root
})

stim.registerCustomElement([
    'carousel',
    'media-player',
    'root'
])
stim.registerSelectorCallback({
    '.l-row': el => {
        if (el.children.length < 3) return
        el.setAttribute('role', 'list')
        el.childNodes.forEach( child => {
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
            if (e.target.tagName === 'A') return
            const link = el.querySelector('a')
            if (e.metaKey) {
                window.open(link.href, '_blank')
                return
            }
            link.click()
        })
    },
})

export { stim, htmx }
