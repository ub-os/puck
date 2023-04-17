import Splide from '@splidejs/splide'
import {getNode} from '../General/Functions';

export default class Carousel {
    constructor(target, {controls, verticalOptions = false, activeClass = '--active', ...options }) {
        Object.assign(this, {controls, verticalOptions, activeClass, ...options })
        this.node = getNode(target, 'Carousel')
        this.id = this.node.id
        this.controls = controls || document.querySelectorAll(`[aria-controls="${this.id}"]`)
        this.biggestSlideHeight = 0
        this.splide = new Splide(this.node, {
            arrows: true,
            pagination: false,
            autoWidth: true,
            omitEnd: true,
            focus: 'left',
            ...options})
    }
    mount() {
        this.splide.mount()
        this.controls.forEach(c => {
            c.addEventListener('click', e => {
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
            window.addEventListener('load', () => {
                this.node.querySelectorAll('.splide__slide').forEach(slide => {
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
}