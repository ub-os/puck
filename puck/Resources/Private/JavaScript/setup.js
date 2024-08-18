import { app } from '~/_jcores'
import htmx from 'htmx.org/dist/htmx.cjs.js'
import ScrollbarWidth from "~/Cores/ScrollbarWidth"
import AnchorBehavior from "~/Cores/AnchorScrolling.js"
import Toggleable from '~/Cores/Showable.js'
import Accordion from '~/Cores/Accordion'
import Modal from "~/Cores/Modal"
import Carousel from '~/Cores/Carousel'
import PageHeader from "~/Cores/PageHeader"
import MediaPlayer from "~/Cores/MediaPlayer"
import ScrollSensitive from '~/Cores/ScrollSensitive'
import FormPage from '~/Cores/FormPage'
import Root from "~/Cores/Root"


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
    observeAttributes: false
})


app.registerAspect({
    AnchorBehavior,
    ScrollbarWidth,
    Toggleable,
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

app.registerEvent({
    'some-event': {
        bubbles: true,
        detail: {
        }
    },
})

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