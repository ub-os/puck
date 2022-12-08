import Splide from '@splidejs/splide'
import {getNode} from '../General/Functions';

export default class Carousel {
    constructor(target, {controls, ...options }) {
        Object.assign(this, {controls, ...options })
        this.node = getNode(target, 'Carousel')
        this.id = this.node.id
        this.controls = controls || document.querySelectorAll(`[aria-controls="${this.id}"]`)
        this.splide = new Splide(this.node, {
            arrows: false,
            autoWidth: true,
            ...options})
    }
    mount() {
        this.splide.mount()
        this.controls.forEach(c => {
            c.addEventListener('click', e => {
                e.preventDefault()
                this.splide.go(c.dataset.goTo)
            })
        })
        return this
    }
}