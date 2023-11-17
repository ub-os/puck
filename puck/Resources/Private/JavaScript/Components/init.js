import { $, $$, jsx } from '../General/Aliases';
import App from '../Classes/App';
import LinkTo from '../Classes/LinkTo';
import SmoothHashLinks from "../Classes/SmoothHashLinks";
//import Toggleable from '../Classes/Toggleable';
//import Accordion from '../Classes/Accordion';
//import Modal from '../Classes/Modal';
//import TabPanel from '../Classes/TabPanel';
//import Carousel from '../Classes/Carousel';
import ScrollReveal from '../Classes/ScrollReveal';
import ResponsiveNavigation from "../Classes/ResponsiveNavigation";
import ScrollSensitive from '../Classes/ScrollSensitive';
import FetchLink from "../Classes/FetchLink";
//import MediaPlayer from "../Classes/MediaPlayer";
import smoothscroll from 'smoothscroll-polyfill';

import LayoutRow from '../Classes/LayoutRow';
import PageHeader from "../Classes/PageHeader";
import { getElement, scrollTo } from '~/General/Utility'
import RichText from "../Classes/RichText";
import ScrollbarManager from "../Classes/ScrollbarManager";

import PuxElement from "../Classes/Behaviors/PuxElement";
import Modal from "../Classes/Behaviors/Modal"
import MediaPlayer from "../Classes/Behaviors/MediaPlayer"
import Link from "../Classes/Behaviors/Link"
import BreakpointModal from "../Classes/Behaviors/BreakpointModal";


// 1. register all mixins
Link.registerAsMixin('link')
Modal.registerAsMixin('modal')
MediaPlayer.registerAsMixin('media-player')

// 2. register the base element
window.customElements.define('pux-el', PuxElement);

// 3. register all elements
Link.registerAsElement('link')
Modal.registerAsElement('modal')
MediaPlayer.registerAsElement('media-player')
BreakpointModal.registerAsElement('breakpoint-modal')



// create pux link element
const puxLink = document.createElement('pux-link')
puxLink.setAttribute('to', 'https://www.google.com',)
puxLink.innerText = 'pux link element'

console.dir(puxLink)

// create pux link element with jsx
const puxLinkJsx = <pux-link to={'https://www.google.com'}>pux link element jsx</pux-link>

// create generic pux element with link mixin via use-link attribute
const puxElWithUseAttr = <pux-el use-link={JSON.stringify({to: 'https://www.google.com'})}>pux element jsx with use link</pux-el>

// create generic pux element with link mixin via use function
const puxElWithAddedMixin = <pux-el>pux element with added mixin</pux-el>
puxElWithAddedMixin.use('link', {to: 'https://www.google.com'})

// create div element with link behavior by manually attaching mixin class
const divLink = <div>div element with attached mixin class</div>
const puxLinkOnDiv = new Link(divLink, {to: 'https://www.google.com'}).mount()

$('main').append(puxLink)
$('main').append(puxLinkJsx)
$('main').append(puxElWithUseAttr)
$('main').append(puxElWithAddedMixin)
$('main').append(divLink)

// disable pux link core functionality
puxLink.destroyCore()

// enable pux link core functionality (is done automatically when added to dom)
puxLink.mountCore()

// disable all pux element mixins
puxElWithAddedMixin.destroyMixins()

// enable all pux element mixins (is done automatically when added to dom)
puxElWithAddedMixin.mountMixins()

// disable pux element link mixin
puxElWithAddedMixin.mixins.link.destroy()

// enable pux element link mixin (is done automatically when added to dom, or when use attribute is added)
puxElWithAddedMixin.mixins.link.mount()

// disable pux element all mixins and core functionality
puxElWithAddedMixin.destroy()

// enable pux element all mixins and core functionality (is done automatically when added to dom)
puxElWithAddedMixin.mount()


smoothscroll.polyfill()


const _app = new App({
    debug: document.body.dataset.appDebug,
    scrollOnCurrentLink: true
})


const mountComponents = (target) => {
    const root = getElement(target)
    _app.managers.push({
        scrollBar: new ScrollbarManager({}).mount()
    })
    _app.components.push({
        layoutRows: LayoutRow.createInstancesFromDataAttribute({ root, attribute: 'data-layout-row' }),
    })
    _app.components.push(
        {
            pageHeader: new PageHeader($('[data-page-header]'), { scrollTops: {800: 25} }).mount(),

            smoothHashLinks: new SmoothHashLinks({ root }).mount(),

            richTexts: RichText.createInstancesFromDataAttribute({ root, attribute: 'data-rich-text' }),

            //linkTos: LinkTo.createInstancesFromDataAttribute({ root, attribute: 'data-link-to' }),

            fetchLinks: FetchLink.createInstancesFromDataAttribute({ root, attribute: 'data-fetch-link' }),

            //accordions: Accordion.createInstancesFromDataAttribute({ root, attribute: 'data-accordion', options: {clickDelay: 150, useMinHeight: true} }),

            //toggleables: Toggleable.createInstancesFromDataAttribute({ root, attribute: 'data-toggleable' }),

            //tabPanels: TabPanel.createInstancesFromDataAttribute({ root, attribute: 'data-tab-panel', options: {clickDelay: 150} }),

            //modals: Modal.createInstancesFromDataAttribute({ root, attribute: 'data-modal', options: {clickDelay: 150} }),

            //carousels: Carousel.createInstancesFromDataAttribute({ root, attribute: 'data-carousel' }),

            scrollSensitives: ScrollSensitive.createInstancesFromDataAttribute({ root, attribute: 'data-scroll-sensitive' }),

            //mediaPlayers: MediaPlayer.createInstancesFromDataAttribute({ root, attribute: 'data-media-player' }),

            responsiveNavigations: ResponsiveNavigation.createInstancesFromDataAttribute({ root, attribute: 'data-responsive-navigation' }),

/*            scrollReveals: [...root.$$('main section')].map(element => {
                return {
                    section: new ScrollReveal(element, {}).mount(),
                    listItems: [...element.$$('.l-card, .m-content-accordions__item')].map((li, index) => {
                        return new ScrollReveal(li, {
                            observationTarget: element,
                            timing: {
                                delay: 50 + index * 100
                            },
                        }).mount()
                    }),
                    media: [...element.$$('.l-media__figure')].map((media, index) => {
                        return new ScrollReveal(media, {
                            observationTarget: element,
                            timing: {
                                delay: 50 + index * 100
                            },
                        }).mount()
                    })
                }
            }),*/
        }
    )
}

mountComponents(document.body)
_app.mount()

window.requestAnimationFrame(() => {
    document.body.classList.remove('u-no-transition')
    $$('.u-initially-hidden').forEach(element => {
        element.classList.remove('u-initially-hidden')
    });
})

export { mountComponents }