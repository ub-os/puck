import App from "~/Application/Application.js";
import ScrollbarWidth from "~/Controller/ScrollbarWidth"
import AnchorBehavior from "~/Controller/AnchorBehavior"
import Toggleable from '~/Controller/Toggleable'
import Accordion from '~/Controller/Accordion'
import Modal from "~/Controller/Modal"
import MainNav from "~/Controller/MainNav.js"
import TabPanel from '~/Controller/TabPanel'
import Carousel from '~/Controller/Carousel'
import LayoutRow from '~/Controller/LayoutRow'
import PageHeader from "~/Controller/PageHeader"
import MediaPlayer from "~/Controller/MediaPlayer"
import FocusTrap from "~/Controller/FocusTrap"
import ScrollReveal from '~/Controller/ScrollReveal'
import ScrollSensitive from '~/Controller/ScrollSensitive'

App.register({
    'anchor-behavior': AnchorBehavior,
    'scrollbar-width': ScrollbarWidth,
    'toggleable': Toggleable,
    'accordion': Accordion,
    'modal': Modal,
    'main-nav': MainNav,
    'tab-panel': TabPanel,
    'carousel': Carousel,
    'layout-row': LayoutRow,
    'page-header': PageHeader,
    'media-player': MediaPlayer,
    'scroll-reveal': ScrollReveal,
    'scroll-sensitive': ScrollSensitive,
    'focus-trap': FocusTrap,
})

App.registerEvent({
    'toggle-off-all': {
        bubbles: true,
        detail: {
            transition: true
        }
    },
})