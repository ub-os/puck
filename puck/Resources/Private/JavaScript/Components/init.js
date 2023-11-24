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
})

document.puckApp.mount()

const docEl = document.documentElement
let visitIsFrameAction = false
let visitIsRestoration = false
let renderIsPostPreviewRender = false

const frameExitAnimation = (
    event,
    keyframes = [{ opacity: 1 }, { opacity: 0 }],
    timing = { duration: 200, easing: 'ease-in-out', fill: 'forwards' }
) => {
   event.target.turboFrameAnimation = event.target.animate(keyframes, timing)
}

const frameEnterAnimation = (
    event,
    keyframes = [{ opacity: 0 }, { opacity: 1 }],
    timing = { duration: 200, easing: 'ease-in-out', fill: 'forwards' }
) => {
    event.preventDefault();
    if (event.target.turboFrameAnimation) {
        event.target.turboFrameAnimation.finished.then(() => {
            event.detail.resume()
            event.target.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 200, easing: 'ease-in-out', fill: 'forwards' })
            event.target.turboFrameAnimation = null
        })
    } else {
        event.detail.resume()
        event.target.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 200, easing: 'ease-in-out', fill: 'forwards' })
    }
}

const frameViewTransition = (event) => {
    if (document.startViewTransition) {
        event.preventDefault();
        document.startViewTransition(() => {
            event.detail.resume();
        });
    }
}

docEl.addEventListener("turbo:click", (event) => {
    console.log(event.type, event)
    if (event.target.hash && event.target.pathname === window.location.pathname) {
        event.preventDefault()
    }
    if (event.target.hasAttribute('data-turbo-action') && event.target.getAttribute('data-turbo-frame') !== '_top') {
        visitIsFrameAction = true
        setTimeout(() => {
            visitIsFrameAction = false
        }, 2000)
    }
})
docEl.addEventListener("turbo:before-fetch-request", (event) => {
    console.log('before-fetch-request', event)
    //frameExitAnimation(event.target)
})
docEl.addEventListener("turbo:before-frame-render", (event) => {
    console.log(event.type, event)
    //frameEnterAnimation(event.target)
    //frameViewTransition(event)
})
docEl.addEventListener("turbo:frame-render", (event) => {
    console.log(event.type, event)
})
docEl.addEventListener("turbo:frame-load", (event) => {
    console.log(event.type, event)
    mountComponents(event.target)
})
docEl.addEventListener("turbo:visit", (event) => {
    console.log(event.type, event)
    if (event.detail.action == 'restore') {
        visitIsRestoration = true
    }
})

let visitFrameActionCacheExcluded = []
docEl.addEventListener("turbo:before-cache", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })

    if (visitIsFrameAction) {
    }
})
docEl.addEventListener("turbo:before-render", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
    if (visitIsRestoration) {
        event.preventDefault()
        event.detail.newBody.$$('[data-turbo-restore-excluded]').forEach(el => el.remove())
        event.detail.resume()
    }
    if (!renderIsPostPreviewRender && !visitIsFrameAction && !visitIsRestoration) {
        //frameEnterAnimation(event.target)
        //frameViewTransition(event)
    }
})
docEl.addEventListener("turbo:render", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
})
docEl.addEventListener("turbo:load", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
    window.dispatchEvent(new Event('scroll'))

    if (visitIsFrameAction) {
        visitIsFrameAction = false
    } else {
        mountBody()
    }

    if (visitIsRestoration) visitIsRestoration = false
    renderIsPostPreviewRender = docEl.hasAttribute('data-turbo-preview')
})

export { mountComponents }