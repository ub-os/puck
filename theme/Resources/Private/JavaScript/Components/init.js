
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
import smoothscroll from 'smoothscroll-polyfill';
import { getParents } from '../General/Functions';

smoothscroll.polyfill();

const _app = new App({
    debug: document.body.dataset.appDebug,
    scrollOnCurrentLink: true
})
_app.components = {

    smoothHashLinks: new SmoothHashLinks().mount(),

    scrollReveals: [...$$('main section')].map(node => {
        return {
            section: new ScrollReveal(node, {}).mount(),
            listItems: [...node.$$('li')].map((li, index) => {
                if (getParents(li, node).length < 9) {
                    return new ScrollReveal(li, {
                        timing: {
                            delay: 50+150*index
                        },
                    }).mount()
                }
                return null
            }),
            media: [...node.$$('img, video')].map((media, index) => {
                return new ScrollReveal(media, {
                    timing: {
                        delay: 350+150*index
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

    fakeLinks: new Map([...$$('[data-link-to]')].map((node, i) => {
        return [i, new FakeLink(node).mount()]
    })),

    toggleables: new Map([...$$('[data-toggleable]')].map(node => {
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

    accordions: new Map([...$$('[data-accordion]')].map(node => {
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

    tabPanels: new Map([...$$('[data-tab-panel]')].map(node => {
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

    modals: new Map([...$$('[data-modal]')].map(node => {
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

    carousels: new Map([...$$('[data-carousel]')].map(node => {
        return [
            node.id,
            new Carousel(
                node,
                {
                ...JSON.parse(node.dataset.carousel || '{}'),
                ...{
                    type: 'loop'
                }}
            ).mount()
        ]
    })),

    scrollSensitives: new Map([...$$('[data-scroll-sensitive]')].map((node, i) => {
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
_app.mount()

window.requestAnimationFrame(() => {
    document.body.classList.remove('u-no-transition');
    $$('.u-initially-hidden').forEach(node => {
        node.classList.remove('u-initially-hidden');
    });
})

