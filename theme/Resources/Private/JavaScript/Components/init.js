
import {$, $$, jsx} from '../General/Aliases';
import App from '../Classes/App';
import {Toggleable} from '../Classes/Toggleable';
import Accordion from '../Classes/Accordion';
import Modal from '../Classes/Modal';
import Carousel from '../Classes/Carousel';
import SmoothHashLinks from "../Classes/SmoothHashLinks";
import ScrollSensitive from '../Classes/ScrollSensitive';
import FakeLink from '../Classes/FakeLink';
import smoothscroll from 'smoothscroll-polyfill';

smoothscroll.polyfill();

const _app = new App({
    debug: document.body.dataset.appDebug
})
_app.components = {

    smoothHashLinks: new SmoothHashLinks().mount(),

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
            new ScrollSensitive(node).mount()
        ]
    })),

    scrollTop: [...$$('[data-to-top]')].map(node => {
        node.on('click', function(){
            window.scrollTo({
                top: 0,
                left: 0,
                behavior: 'smooth'
            })
        })
        return node
    })
}
_app.mount()
