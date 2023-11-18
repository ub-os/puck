import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, id$, jsx } from '~/General/Aliases'
import { kebabCase } from "~/General/Utility"
import ScrollbarManager from "~/Classes/ScrollbarManager"
import LinkManager from "~/Classes/LinkManager"
import PuxElement from "~/Classes/Behaviors/PuxElement"
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
import ScrollReveal from '~/Classes/Behaviors/ScrollReveal'
import ScrollSensitive from '~/Classes/Behaviors/ScrollSensitive'

smoothscroll.polyfill();

// 1. register behaviors as mixins
[ Toggleable, Accordion, Modal, BreakpointModal, TabPanel, Carousel, Link, FetchLink,
    LayoutRow, PageHeader, RichText, MediaPlayer, ScrollReveal, ScrollSensitive ].forEach(Behavior => {
    Behavior.registerAsMixin(kebabCase(Behavior.name))
})

// 2. register the base element
window.customElements.define('pux-el', PuxElement);

// 3. register behaviors as elements
[ Accordion, Modal, BreakpointModal, TabPanel, Carousel, Link, FetchLink,
    LayoutRow, RichText, MediaPlayer ].forEach(Behavior => {
    Behavior.registerAsElement(kebabCase(Behavior.name))
})


const _puckApp = {
    managers: {
        scrollbar: new ScrollbarManager({}).mount(),
        link: new LinkManager({}).mount(),
    },
    getBehaviors: () => {
    }
}
console.log(_puckApp)

window.requestAnimationFrame(() => {
    document.body.classList.remove('u-no-transition')
    $$('.u-initially-hidden').forEach(element => {
        element.classList.remove('u-initially-hidden')
    });
})

export default _puckApp