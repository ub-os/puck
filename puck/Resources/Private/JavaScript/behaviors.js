import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, id$, jsx } from '~/General/Aliases'
import { kebabCase, jsonParse } from "~/General/Utility"
import { ObserverManager, MutationManager } from "~/Classes/ObserverManager"
import ScrollbarManager from "~/Classes/ScrollbarManager"
import LinkManager from "~/Classes/LinkManager"
import PuxElement from "~/Classes/Behaviors/PuxElement"
import NativePuxElement from "~/Classes/Behaviors/NativePuxElement"
import Toggleable from '~/Classes/Behaviors/Toggleable'
import Accordion from '~/Classes/Behaviors/Accordion'
import Modal from "~/Classes/Behaviors/Modal"
import BreakpointModal from "~/Classes/Behaviors/BreakpointModal"
import TabPanel from '~/Classes/Behaviors/TabPanel'
import Carousel from '~/Classes/Behaviors/Carousel'
import Link from "~/Classes/Behaviors/Link"
import FetchLink from "~/Classes/Behaviors/FetchLink"
import LayoutRow from '~/Classes/Behaviors/LayoutRow'
import PageHeader from "~/Classes/Behaviors/PageHeader"
import RichText from "~/Classes/Behaviors/RichText"
import MediaPlayer from "~/Classes/Behaviors/MediaPlayer"
import FocusTrap from "~/Classes/Behaviors/FocusTrap"
import ScrollReveal from '~/Classes/Behaviors/ScrollReveal'
import ScrollSensitive from '~/Classes/Behaviors/ScrollSensitive'
import App from "~/Classes/Application"

smoothscroll.polyfill();

// 1. register behaviors as mixins
[ Toggleable, Accordion, Modal, BreakpointModal, TabPanel, Carousel, Link, FetchLink,
    LayoutRow, PageHeader, RichText, MediaPlayer, ScrollReveal, ScrollSensitive, FocusTrap ].forEach(Behavior => {
    // todo: name will me mangled in production
    Behavior.registerAsMixin(kebabCase(Behavior.name))
})

// 2. register the base element
window.customElements.define('pux-el', PuxElement);

// 3. register behaviors as elements
[ Toggleable, Accordion, Modal, BreakpointModal, TabPanel, Carousel, Link, FetchLink,
    LayoutRow, RichText, MediaPlayer ].forEach(Behavior => {
    // todo: name will me mangled in production
    Behavior.registerAsElement(kebabCase(Behavior.name))
})

// 4. mount mixins  on built-in elements
const startBuiltIntElementConnection = () => {
    MutationManager.addById(
        'built-in-element-connection',
        document.body,
        mutations => {
            mutations.forEach(mutation => {
                if (mutation.type !== 'childList') return
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE && node.hasAttribute('data-pux-el')) {
                        node.puxEl = new NativePuxElement(node).mount()
                    }
                })
                mutation.removedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE && node.hasAttribute('data-pux-el')) {
                        node.puxEl.destroy()
                    }
                })
            })
        },
        { childList: true, subtree: true },
    )
    $$('[data-pux-el]').forEach(el => {
        el.puxEl = new NativePuxElement(el).mount()
    })
}

const startManagers = () => {
    App.managers.scrollbar = new ScrollbarManager({}).mount()
    App.managers.link = new LinkManager({}).mount()
}

const startBody = () => {
    App.observerManager.inst
    startBuiltIntElementConnection()
    startManagers()
    window.requestAnimationFrame(() => {
        document.body.classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => element.classList.remove('u-initially-hidden'))
    })
    console.log(App)
}

export default startBody