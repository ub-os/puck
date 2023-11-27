import ControllerCollection from "~/Application/ControllerCollection"
import AnchorBehavior from "~/Controller/AnchorBehavior"
import Toggleable from '~/Controller/Toggleable'
import Accordion from '~/Controller/Accordion'
import Modal from "~/Controller/Modal"
import BreakpointModal from "~/Controller/BreakpointModal"
import TabPanel from '~/Controller/TabPanel'
import Carousel from '~/Controller/Carousel'
import Link from "~/Controller/Link"
import FetchLink from "~/Controller/FetchLink"
import LayoutRow from '~/Controller/LayoutRow'
import PageHeader from "~/Controller/PageHeader"
import RichText from "~/Controller/RichText"
import MediaPlayer from "~/Controller/MediaPlayer"
import FocusTrap from "~/Controller/FocusTrap"
import ScrollReveal from '~/Controller/ScrollReveal'
import ScrollSensitive from '~/Controller/ScrollSensitive'

ControllerCollection.register('anchor-behavior', AnchorBehavior)
ControllerCollection.register('toggleable', Toggleable)
ControllerCollection.register('accordion', Accordion)
ControllerCollection.register('modal', Modal)
ControllerCollection.register('breakpoint-modal', BreakpointModal)
ControllerCollection.register('tab-panel', TabPanel)
ControllerCollection.register('carousel', Carousel)
ControllerCollection.register('link', Link)
ControllerCollection.register('fetch-link', FetchLink)
ControllerCollection.register('layout-row', LayoutRow)
ControllerCollection.register('page-header', PageHeader)
ControllerCollection.register('rich-text', RichText)
ControllerCollection.register('media-player', MediaPlayer)
ControllerCollection.register('scroll-reveal', ScrollReveal)
ControllerCollection.register('scroll-sensitive', ScrollSensitive)
ControllerCollection.register('focus-trap', FocusTrap)