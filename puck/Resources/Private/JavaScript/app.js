import App from "~/_Stim/Application"
import ScrollbarWidth from "~/Controller/ScrollbarWidth"
import AnchorBehavior from "~/Controller/AnchorBehavior"
import Toggleable from '~/Controller/Toggleable'
import Accordion from '~/Controller/Accordion'
import Modal from "~/Controller/Modal"
import MainNav from "~/Controller/MainNav"
import TabPanel from '~/Controller/TabPanel'
import Carousel from '~/Controller/Carousel'
import PageHeader from "~/Controller/PageHeader"
import MediaPlayer from "~/Controller/MediaPlayer"
import FocusTrap from "~/Controller/FocusTrap"
import ScrollReveal from '~/Controller/ScrollReveal'
import ScrollSensitive from '~/Controller/ScrollSensitive'


App.registerController({
    AnchorBehavior,
    ScrollbarWidth,
    Toggleable,
    Accordion,
    Modal,
    MainNav,
    TabPanel,
    Carousel,
    PageHeader,
    MediaPlayer,
    ScrollReveal,
    ScrollSensitive,
    FocusTrap,
})

App.registerEvent({
    'toggle-off-all': {
        bubbles: true,
        detail: {
            transition: true
        }
    },
})

App.registerConnectedCallback({
    '.l-row': (el) => {
        if (el.children.length < 3) return
        el.setAttribute('role', 'list')
        el.childNodes.forEach( child => {
            if (child.nodeType !== 1) return
            child.setAttribute('role', 'listitem')
        })
    },
    'a': (el) => {
        if (el.hostname === window.location.hostname) {
            el.classList.add('-local');
        } else {
            el.classList.add('-external');
        }
    }
})