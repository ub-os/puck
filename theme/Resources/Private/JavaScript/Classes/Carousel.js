import Splide from '@splidejs/splide'

export default class Carousel {
    constructor(target, {controls, ...options }) {
        Object.assign(this, {controls, ...options })
        if (target instanceof Element) {
            this.node = target
        } else if (typeof target === 'string' && document.getElementById(target)) {
            this.node = document.querySelector(target)
        } else {
            console.error('Carousel: No valid element or id for root node provided')
            console.trace()
        }
        this.id = this.node.id
        this.controls = controls || document.querySelectorAll(`[aria-controls="${this.id}"]`)
        this.splide = new Splide(this.node, options)
    }
    mount() {
        this.splide.mount()
        this.controls.forEach(c => {
            c.ariaControls = this.id
            c.addEventListener('click', e => {
                e.preventDefault()
                this.splide.go(c.dataset.goTo)
            })
        })
        return this
    }
}