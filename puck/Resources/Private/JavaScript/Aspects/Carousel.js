import Splide from '@splidejs/splide'
import { ElementAspect } from "~/_jcores"
import EventHandlerSet from "~/Helper/EventHandlerSet"

/**
 * @property {Map} controlElements
 */
export default class Carousel extends ElementAspect {
    static attributes = {
        vertical: false,
        activeClass: '--active',
        splideOptions: {},
    }
    static connectedElements = ['control']
    //handlerSet = new EventHandlerSet()
    controlElementConnected(el) {
        el.ariaControls = this.el.id
        if (el.dataset['carousel.move.to'] == this.splide?.index) {
            el.classList.add(this.activeClass)
        }
    }
    move({ to } = {}) {
        this.splide.go(to)
    }
    connected() {
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
    disconnected() {
        this.splide.destroy()
    }
}
