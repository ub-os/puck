import Nexus from "~/_jcores/Nexus"
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
import htmx from '~/htmx'


Nexus.registerCore({
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

Nexus.registerEvent({
    'toggle-off-all': {
        bubbles: true,
        detail: {
            transition: true
        }
    },
})

Nexus.registerConnectedCallback({
    '.l-row': (el) => {
        if (el.children.length < 3) return
        el.setAttribute('role', 'list')
        el.childNodes.forEach( child => {
            if (child.nodeType !== 1) return
            child.setAttribute('role', 'listitem')
        })
    },
    'a': el => {
        if (el.hash?.substring(1).split('?')[0] && el.pathname === window.location.pathname) {
            el.classList.add('-current-hash')
            el.setAttribute('data-hx-boost', 'false')
            htmx.process(el)
        }
    },
})

export default Nexus