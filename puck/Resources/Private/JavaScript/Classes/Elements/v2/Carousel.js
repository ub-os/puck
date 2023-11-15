import Splide from '@splidejs/splide'
import Listeners from "../../Listeners.js"
import AbstractBehavior from "./AbstractBehavior.js";

export default class Carousel extends AbstractBehavior {
    static props = {
        controlEls: [],
        vertical: false,
        activeClass: '--active',
        splideOptions: {},
    }
    mount() {
        super.mount()
        this.controlEls = document.querySelectorAll(`[aria-controls="${this.el.id}"]`)
        this.listeners = new Listeners()
        this.biggestSlideHeight = 0
        this.splide = new Splide(this.el, {
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
                this.el.querySelectorAll('.splide__slide').forEach(slide => {
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
