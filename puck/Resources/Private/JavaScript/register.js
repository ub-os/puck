import App from "~/Application/Application.js";
import ScrollbarWidth from "~/Controller/ScrollbarWidth"
import AnchorBehavior from "~/Controller/AnchorBehavior"
import Toggleable from '~/Controller/Toggleable'
import Accordion from '~/Controller/Accordion'
import Modal from "~/Controller/Modal"
import MainNav from "~/Controller/MainNav.js"
import TabPanel from '~/Controller/TabPanel'
import Carousel from '~/Controller/Carousel'
import Link from "~/Controller/Link"
import FetchLink from "~/Controller/FetchLink"
import LayoutRow from '~/Controller/LayoutRow'
import PageHeader from "~/Controller/PageHeader"
import MediaPlayer from "~/Controller/MediaPlayer"
import FocusTrap from "~/Controller/FocusTrap"
import ScrollReveal from '~/Controller/ScrollReveal'
import ScrollSensitive from '~/Controller/ScrollSensitive'

App.register('anchor-behavior', AnchorBehavior)
App.register('scrollbar-width', ScrollbarWidth)
App.register('toggleable', Toggleable)
App.register('accordion', Accordion)
App.register('modal', Modal)
App.register('main-nav', MainNav)
App.register('tab-panel', TabPanel)
App.register('carousel', Carousel)
App.register('link', Link)
App.register('fetch-link', FetchLink)
App.register('layout-row', LayoutRow)
App.register('page-header', PageHeader)
App.register('media-player', MediaPlayer)
App.register('scroll-reveal', ScrollReveal)
App.register('scroll-sensitive', ScrollSensitive)
App.register('focus-trap', FocusTrap)