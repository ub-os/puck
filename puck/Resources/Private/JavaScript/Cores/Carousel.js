import Splide from '@splidejs/splide'
import { Core } from "~/_jcores"

/**
 * @property {Map} controlElements
 */
export default class Carousel extends Core {
    static attributes = {
        vertical: false,
        activeClass: '--active',
        splideOptions: {},
    }
    static elements = ['control']
    controlElementConnected(el) {
        el.ariaControls = this.el.id
        if (el.dataset['carousel.move.to'] == this.splide?.index) {
            el.classList.add(this.activeClass)
        }
    }
    move(event, { to }) {
        this.splide.go(to)
    }
    connect() {
        this.biggestSlideHeight = 0
        this.splide = new Splide(this.el, {
            arrows: true,
            pagination: false,
            autoWidth: true,
            omitEnd: true,
            focus: 'left',
            ...this.splideOptions })
        this.splide.on('move', (newIndex, oldIndex, destIndex) => {
            this.controlElements.forEach(control => {
                control.classList.remove(this.activeClass)
                if (control.dataset['carousel::move:to'] == this.splide?.index) {
                    control.classList.add(this.activeClass)
                }
            })
        })
        this.splide.on( 'pagination:mounted', data => {
            data.list.setAttribute('data-render-excluded', '')
        } )
        this.splide.mount()
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
    disconnect() {
        this.splide.destroy()
        this.listeners.destroy()
    }
}
