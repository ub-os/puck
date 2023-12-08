import Splide from '@splidejs/splide'
import { $, $$, jsx } from '~/Utility/DomUtility'
import Listeners from "~/Service/Listeners"
import Controller from "~/Application/Controller.js";

export default class Carousel extends Controller {
    static props = {
        vertical: false,
        activeClass: '--active',
        splideOptions: {},
    }
    connect() {
        super.connect()
        this.controlEls = $$(`[aria-controls="${this.el.id}"]`)
        this.listeners = new Listeners()
        this.biggestSlideHeight = 0
        this.splide = new Splide(this.el, {
            arrows: true,
            pagination: false,
            autoWidth: true,
            omitEnd: true,
            focus: 'left',
            ...this.splideOptions })
        this.splide.on('move', (newIndex, oldIndex, destIndex) => {
            this.controlEls.forEach(c => {
                c.classList.remove(this.activeClass)
                if (c.dataset.goTo == newIndex) {
                    c.classList.add(this.activeClass)
                }
            })
        })
        this.splide.on( 'pagination:mounted', function ( data ) {
            data.list.setAttribute('data-turbo-render-excluded', '')
        } )
        this.controlEls.forEach(c => {
            if (!c.dataset.goTo) return
            this.listeners.add(c, 'click', e => {
                e.preventDefault()
                this.splide.go(parseInt(c.dataset.goTo))
            })
        })
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
