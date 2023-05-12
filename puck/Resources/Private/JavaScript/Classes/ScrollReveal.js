import ScrollSensitive from "./ScrollSensitive.js";

export default class ScrollReveal extends ScrollSensitive {
    static events = {
        ...super.events,
        scrollReveal: new Event('scrollReveal'),
    }
    static defaultObserverOptions = {
        root: null,
        rootMargin: '0px 0px -40px 0px',
        threshold: 0,
    }
    static createIntersectionObserver = (options = this.defaultObserverOptions) => {
        const observer =  new IntersectionObserver(
            ([e]) => {
                if (e.isIntersecting) {
                    observer.unobserve(e.target)
                    e.target.dispatchEvent(this.events.scrollReveal)
                }
            },
            options)
        return observer
    }
    static defaultObserver = this.createIntersectionObserver()

    constructor(target, {
        preset = 'slide-up',
        presetTranslate = 10,
        animation = null,
        timing = {},
        ...options})
    {
        super(target, { ...options })
        Object.assign(this, { preset, presetTranslate, animation })
        this.timing = {
            duration: 500,
            easing: 'ease-in-out',
            ...timing
        }
        this.revealed = true
        if (!this.animation) {
            this.animation = this.getAnimationPresets(this.preset)
        }
    }
    getAnimationPresets(preset) {
        const presets = {
            'slide-up': [
                { transform: `translateY(${this.presetTranslate}px)`, opacity: 0 },
                { transform: `translateY(0)`, opacity: 1 },
            ],
            'slide-down': [
                { transform: `translateY(-${this.presetTranslate}px)`, opacity: 0 },
                { transform: `translateY(0)`, opacity: 1 },
            ],
            'slide-left': [
                { transform: `translateX(${this.presetTranslate}px)`, opacity: 0 },
                { transform: `translateX(0)`, opacity: 1 },
            ],
            'slide-right': [
                { transform: `translateX(-${this.presetTranslate}px)`, opacity: 0 },
                { transform: `translateX(0)`, opacity: 1 },
            ],
            'fade-in': [
                { opacity: 0 },
                { opacity: 1 },
            ]
        }
        return presets[preset] || presets['fade-in']
    }
    reveal() {
        this.revealed = true
        this.animate()
    }
    animate() {
        const animation = this.node.animate(this.animation, this.timing)
        animation.addEventListener('finish', () => {
            this.node.style.opacity = 1
        })
    }
    mount() {
        window.requestAnimationFrame(() => {
            if (this.observedNode.getBoundingClientRect().top > window.innerHeight) {
                this.node.style.opacity = 0
                this.revealed = false
            }
            if (this.revealed) return this
            super.mount()
            this.observedNode.addEventListener('scrollReveal', () => {
                this.reveal()
            })
            return this
        })
    }
}