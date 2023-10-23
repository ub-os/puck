import { $, $$, jsx } from '../General/Aliases';
import App from '../Classes/App';
import LinkTo from '../Classes/LinkTo';
import Toggleable from '../Classes/Toggleable';
import Accordion from '../Classes/Accordion';
import Modal from '../Classes/Modal';
import TabPanel from '../Classes/TabPanel';
import Carousel from '../Classes/Carousel';
import ScrollSensitive from '../Classes/ScrollSensitive';
import FetchLink from "../Classes/FetchLink.js";
import MediaPlayer from "../Classes/MediaPlayer";
import smoothscroll from 'smoothscroll-polyfill';
import LayoutRow from '../Classes/LayoutRow';
import PageHeader from "../Classes/PageHeader";
import { getElement } from '../General/Functions';
import RichText from "../Classes/RichText.js";
import ResponsiveNavigation from "../Classes/ResponsiveNavigation.js";
import { ObserverManager } from "../Classes/ObserverManager.js";
import ScrollbarManager from "../Classes/ScrollbarManager.js";
import LinkManager from "../Classes/LinkManager.js";
import * as Turbo from "@hotwired/turbo"


const mountComponents = (target) => {
    const root = getElement(target)
    document.puckApp.components.push({
        layoutRows: LayoutRow.createInstancesFromDataAttribute({ root, attribute: 'data-layout-row' }),
    })
    document.puckApp.components.push(
        {
            pageHeader: new PageHeader( $('[data-page-header]'), { scrollTops: {800: 25} }).mount(),

            richTexts: RichText.createInstancesFromDataAttribute({ root, attribute: 'data-rich-text' }),

            linkTos: LinkTo.createInstancesFromDataAttribute({ root, attribute: 'data-link-to' }),

            fetchLinks: FetchLink.createInstancesFromDataAttribute({ root, attribute: 'data-fetch-link' }),

            accordions: Accordion.createInstancesFromDataAttribute({ root, attribute: 'data-accordion', options: {clickDelay: 150, useMinHeight: true} }),

            toggleables: Toggleable.createInstancesFromDataAttribute({ root, attribute: 'data-toggleable' }),

            tabPanels: TabPanel.createInstancesFromDataAttribute({ root, attribute: 'data-tab-panel', options: {clickDelay: 150} }),

            modals: Modal.createInstancesFromDataAttribute({ root, attribute: 'data-modal', options: {clickDelay: 150} }),

            carousels: Carousel.createInstancesFromDataAttribute({ root, attribute: 'data-carousel' }),

            scrollSensitives: ScrollSensitive.createInstancesFromDataAttribute({ root, attribute: 'data-scroll-sensitive' }),

            mediaPlayers: MediaPlayer.createInstancesFromDataAttribute({ root, attribute: 'data-media-player' }),

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

const mountBody = () => {
    // add stuff here that should run on page load
    document.puckApp.managers.links = new LinkManager({}).mount()
    document.puckApp.managers.scrollbar = new ScrollbarManager({}).mount()
    mountComponents(document.body)
    window.requestAnimationFrame(() => {
        document.body.classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => {
            element.classList.remove('u-initially-hidden')
        });
    })
}

smoothscroll.polyfill()
document.puckApp = new App({
    debug: document.body.dataset.appDebug,
    scrollOnCurrentLink: true
})

document.puckApp.mount()

document.documentElement.addEventListener("turbo:load", (event) => {
    mountBody()
})
document.documentElement.addEventListener("turbo:before-render", (event) => {
    if (document.startViewTransition) {
        event.preventDefault();
        document.startViewTransition(() => {
            event.detail.resume();
        });
    }
})
document.documentElement.addEventListener("turbo:before-cache", (event) => {
})



export { mountComponents }