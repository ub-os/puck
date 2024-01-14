import App from "~/_htmc/Application"
import ScrollbarWidth from "~/Cores/ScrollbarWidth"
import AnchorBehavior from "~/Cores/AnchorBehavior"
import Toggleable from '~/Cores/Toggleable'
import Accordion from '~/Cores/Accordion'
import Modal from "~/Cores/Modal"
import MainNav from "~/Cores/MainNav"
import TabPanel from '~/Cores/TabPanel'
import Carousel from '~/Cores/Carousel'
import PageHeader from "~/Cores/PageHeader"
import MediaPlayer from "~/Cores/MediaPlayer"
import FocusTrap from "~/Cores/FocusTrap"
import ScrollReveal from '~/Cores/ScrollReveal'
import ScrollSensitive from '~/Cores/ScrollSensitive'


App.registerCore({
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