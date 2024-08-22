import { app } from '~/_jcores'
import htmx from 'htmx.org/dist/htmx.cjs.js'
import ScrollbarWidth from "~/Aspects/ScrollbarWidth"
import AnchorScrolling from "~/Aspects/AnchorScrolling"
import Showable from '~/Aspects/Showable'
import Accordion from '~/Aspects/Accordion'
import Modal from "~/Aspects/Modal"
import Carousel from '~/Aspects/Carousel'
import PageHeader from "~/Aspects/PageHeader"
import MediaPlayer from "~/Aspects/MediaPlayer"
import ScrollSensitive from '~/Aspects/ScrollSensitive'
import FormPage from '~/Aspects/FormPage'
import Root from "~/Aspects/Root"

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

window.app = app
Object.assign(app.config, {
    attributePrefix: 'data-',
    //observeAttributes: false,
    customElementPrefix: 'pk-'
})

app.registerAspect({
    AnchorScrolling,
    ScrollbarWidth,
    Showable,
    Accordion,
    Modal,
    Carousel,
    PageHeader,
    MediaPlayer,
    ScrollSensitive,
    FormPage,
    Root
})

app.registerAspectCustomElement([
    'carousel',
    'media-player',
    'root'
])

app.registerConnectedCallback({
    '.l-row': (el) => {
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
    }
})


export { app, htmx }
