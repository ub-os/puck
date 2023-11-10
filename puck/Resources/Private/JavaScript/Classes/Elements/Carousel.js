import Splide from '@splidejs/splide'
import PuxElement from "./PuxElement.js";
import Listeners from "../Listeners.js"

export default class Carousel extends PuxElement {
    static props = {
        ...PuxElement.props,
        controlEls: [],
        vertical: false,
        activeClass: '--active',
        splideOptions: {},
    }
    constructor() {
        super()
    }
    mount() {
        this.controlEls = document.querySelectorAll(`[aria-controls="${this.id}"]`)
        this.listeners = new Listeners()
        this.biggestSlideHeight = 0
        this.splide = new Splide(this, {
            arrows: true,
            pagination: false,
            autoWidth: true,
            omitEnd: true,
            focus: 'left',
            ...this.splideOptions }).mount()
        this.controlEls.forEach(c => {
            if (!c.dataset.goTo) return
            this.listeners.add(c, 'click', e => {
                e.preventDefault()
                this.splide.go(parseInt(c.dataset.goTo))
            })
        })
        this.splide.on('move', (newIndex, oldIndex, destIndex) => {
            this.controlEls.forEach(c => {
                c.classList.remove(this.activeClass)
                if (c.dataset.goTo == newIndex) {
                    c.classList.add(this.activeClass)
                }
            })
        })
        if (this.vertical) {
            // todo: replace with window.requestAnimationFrame ?
            window.addEventListener('load', () => {
                this.querySelectorAll('.splide__slide').forEach(slide => {
                    const rect = slide.getBoundingClientRect()
                    if (rect.height > this.biggestSlideHeight) {
                        this.biggestSlideHeight = rect.height
                    }
                })
                this.splide.options = {
                    direction: 'ttb',
                    height: this.biggestSlideHeight,
                }
            })
        }
        return this
    }
    destroy() {
        this.splide.destroy()
        this.listeners.destroy()
    }
}

window.customElements.define('pux-carousel', Carousel);