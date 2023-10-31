import Splide from '@splidejs/splide'
import AbstractComponent from "./AbstractComponent.js"
import Listeners from "./Listeners.js"

export default class Carousel extends AbstractComponent {
    constructor(target, {controls, verticalOptions = false, activeClass = '--active', ...options }) {
        super(target)
        Object.assign(this, {controls, verticalOptions, activeClass, ...options })
        this.controls = controls || document.querySelectorAll(`[aria-controls="${this.id}"]`)
        this.biggestSlideHeight = 0
        this.splide = new Splide(this.element, {
            arrows: true,
            pagination: false,
            autoWidth: true,
            omitEnd: true,
            focus: 'left',
            ...options})
    }
    mount() {
        this.splide.mount()
        this.listeners = new Listeners()
        this.controls.forEach(c => {
            if (!c.dataset.goTo) return
            this.listeners.add(c, 'click', e => {
                e.preventDefault()
                this.splide.go(parseInt(c.dataset.goTo))
            })
        })
        this.splide.on('move', (newIndex, oldIndex, destIndex) => {
            this.controls.forEach(c => {
                c.classList.remove(this.activeClass)
                if (c.dataset.goTo == newIndex) {
                    c.classList.add(this.activeClass)
                }
            })
        })
        if (this.verticalOptions) {
            // todo: replace with window.requestAnimationFrame ?
            window.addEventListener('load', () => {
                this.element.querySelectorAll('.splide__slide').forEach(slide => {
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
        delete this.splide
    }
}