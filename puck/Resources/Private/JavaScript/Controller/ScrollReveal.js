import { IntersectionManager } from "~/Service/ObserverCollector"
import Controller from "~/Application/Controller";

export default class ScrollReveal extends Controller {
    static events = {
        addScrollClass: new Event('add-scroll-class'),
        removeScrollClass: new Event('remove-scroll-class'),
        scrollReveal: new Event('scroll-reveal'),
    }
    static attributes = {
        preset: 'slide-up',
        presetTranslate: 10,
        animation: null,
        observer: {},
        timing: {},
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
        const animation = this.el.animate(this.animation, this.timing)
        this.listeners.add(animation, 'finish', () => {
            this.el.style.opacity = 1
        })
    }
    connect() {
        this.observer = {
            root: null,
            rootMargin: '0px 0px -40px 0px',
            threshold: [0,1],
            target : null,
            ...this.observer
        }
        this.timing = {
            duration: 500,
            easing: 'ease-in-out',
            ...this.timing
        }
        this.revealed = true
        if (!this.animation) {
            this.animation = this.getAnimationPresets(this.preset)
        }
        window.requestAnimationFrame(() => {
            if (this.el.getBoundingClientRect().top > window.innerHeight) {
                this.el.style.opacity = 0
                this.revealed = false
            }
            if (this.revealed) return this
            IntersectionManager.addById(
                'scroll-reveal-' + this.el.id,
                this.el,
                (entry, observer) => {
                    if (entry.isIntersecting) {
                        entry.target.dispatchEvent(this.constructor.events.scrollReveal)
                        IntersectionManager.remove('scroll-reveal-' + this.el.id)
                    }
                },
                {
                    root: this.observer.root,
                    rootMargin: this.observer.rootMargin,
                    threshold: this.observer.threshold
                }
            )
            this.listeners.add(this.el, 'scrollReveal', () => {
                this.reveal()
            })
            return this
        })
    }

    disconnect() {
        this.listeners.destroy()
        IntersectionManager.remove('scroll-reveal-' + this.el.id)
    }
}
