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
import Test from "~/Aspects/Test"
import Test2 from "~/Aspects/Test2"

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
    Root,
    Test, Test2
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

const host = document.getElementById('host')
window.test = {}


window.test.addRemote = () => {
    console.log('add remote')
    const remote = document.createElement('div')
    remote.setAttribute('data-connect', 'test.remote#host')
    document.body.appendChild(remote)
}

window.test.addDescendant = () => {
    console.log('add descendant')
    const descendant = document.createElement('div')
    descendant.setAttribute('data-connect', 'test.descendant')
    host.appendChild(descendant)
}

window.test.removeRemote = () => {
    console.log('remove remote')
    const remote = document.querySelector('[data-connect="test.remote#host"]')
    remote.remove()
}

window.test.removeDescendant = () => {
    console.log('remove descendant')
    const descendant = document.querySelector('[data-connect="test.descendant"]')
    descendant.remove()
}

window.test.removeHost = () => {
    console.log('remove host')
    host.remove()
}

window.test.addHost = () => {
    console.log('add host')
    document.body.appendChild(host)
}

window.test.removeHostConnectIdentifier = () => {
    console.log('remove host connect identifier')
    host.setAttribute('data-connect', '')
}

window.test.addHostConnectIdentifier = () => {
    console.log('add host connect identifier')
    host.setAttribute('data-connect', 'test')
}

window.test.aspect = () => host.nxs_aspects?.get('test')


export { app, htmx }
