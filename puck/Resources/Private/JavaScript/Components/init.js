import anime from 'animejs/lib/anime.js';
import { $, $$, jsx } from '../General/Aliases';
import App from '../Classes/App';
import { Toggleable } from '../Classes/Toggleable';
import Accordion from '../Classes/Accordion';
import Modal from '../Classes/Modal';
import TabPanel from '../Classes/TabPanel';
import Carousel from '../Classes/Carousel';
import FakeLink from '../Classes/FakeLink';
import SmoothHashLinks from "../Classes/SmoothHashLinks";
import { ScrollReveal } from '../Classes/ScrollReveal';
import ScrollSensitive from '../Classes/ScrollSensitive';
import FetchLink from "../Classes/FetchLink.js";
import smoothscroll from 'smoothscroll-polyfill';
import { getNode, scrollTo } from '../General/Functions';

smoothscroll.polyfill()

const _app = new App({
    debug: document.body.dataset.appDebug,
    scrollOnCurrentLink: true
})

const mountComponents = (target) => {
    const root = getNode(target)
    _app.components.push(
        {
            smoothHashLinks: new SmoothHashLinks({root: root}).mount(),

            scrollReveals: [...root.$$('main section')].map(node => {
                return {
                    section: new ScrollReveal(node, {}).mount(),
                    listItems: [...node.$$('.l-card, .m-content-accordions__item')].map((li, index) => {
                        return new ScrollReveal(li, {
                            observationTarget: node,
                            timing: {
                                delay: 50 + index * 100
                            },
                        }).mount()
                    }),
                    media: [...node.$$('.l-media__figure')].map((media, index) => {
                        return new ScrollReveal(media, {
                            observationTarget: node,
                            timing: {
                                delay: 50 + index * 100
                            },
                        }).mount()
                    })
                }
            }),

            burgerMenu: new Toggleable('main-menu',
                {
                    clickDelay: 300,
                    toggleOffOnOutsideClick: true,
                }).mount(),

            fakeLinks: new Map([...root.$$('[data-link-to]')].map((node, i) => {
                return [i, new FakeLink(node).mount()]
            })),

            fetchLinks: new Map([...root.$$('[data-fetch-link]')].map((node, i) => {
                return [
                    node.id || i,
                    new FetchLink(
                        node,
                        {
                            ...JSON.parse(node.dataset.fetchLink || '{}'),
                            ...{

                            }}
                    ).mount()
                ]
            })),

            toggleables: new Map([...root.$$('[data-toggleable]')].map(node => {
                return [
                    node.id,
                    new Toggleable(
                        node,
                        {
                            ...JSON.parse(node.dataset.toggleable || '{}'),
                            ...{

                            }}
                    ).mount()
                ]
            })),

            accordions: new Map([...root.$$('[data-accordion]')].map(node => {
                return [
                    node.id,
                    new Accordion(
                        node,
                        {
                            ...JSON.parse(node.dataset.accordion || '{}'),
                            ...{
                                clickDelay: 150,
                            }}
                    ).mount()
                ]
            })),

            tabPanels: new Map([...root.$$('[data-tab-panel]')].map(node => {
                return [
                    node.id,
                    new TabPanel(
                        node,
                        {
                            ...JSON.parse(node.dataset.tabPanel || '{}'),
                            ...{
                                clickDelay: 150,
                            }}
                    ).mount()
                ]
            })),

            modals: new Map([...root.$$('[data-modal]')].map(node => {
                return [
                    node.id,
                    new Modal(
                        node,
                        {
                            ...JSON.parse(node.dataset.modal || '{}'),
                            ...{
                                clickDelay: 150
                            }}
                    ).mount()
                ]
            })),

            carousels: new Map([...root.$$('[data-carousel]')].map(node => {
                return [
                    node.id,
                    new Carousel(
                        node,
                        {
                            ...JSON.parse(node.dataset.carousel || '{}'),
                            ...{}
                        }
                    ).mount()
                ]
            })),

            scrollSensitives: new Map([...root.$$('[data-scroll-sensitive]')].map((node, i) => {
                return [
                    node.id || i,
                    new ScrollSensitive(
                        node,
                        {
                            ...JSON.parse(node.dataset.scrollSensitive),
                            ...{

                            }}
                    ).mount()
                ]
            })),
        }
    )
}

mountComponents(document.body)
_app.mount()

window.requestAnimationFrame(() => {
    document.body.classList.remove('u-no-transition')
    $$('.u-initially-hidden').forEach(node => {
        node.classList.remove('u-initially-hidden')
    });
})

export {mountComponents}