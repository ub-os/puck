import AbstractComponent from "./AbstractComponent.js";
import Listeners from "./Listeners.js";
import { IntersectionManager } from "./ObserverManagerV2.js";

const scrollRevealObserverMap = new Map()

export default class ScrollReveal extends AbstractComponent {
    static events = {
        addScrollClass: new Event('addScrollClass'),
        removeScrollClass: new Event('removeScrollClass'),
        scrollReveal: new Event('scrollReveal'),
    }

    constructor(target, {
        preset = 'slide-up',
        presetTranslate = 10,
        animation = null,
        observer = {},
        timing = {},
        ...options})
    {
        super(target)
        Object.assign(this, { preset, presetTranslate, animation })
        this.observer = {
            root: null,
            rootMargin: '0px 0px -40px 0px',
            threshold: [0,1],
            target : null,
            ...observer
        }
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
        const animation = this.element.animate(this.animation, this.timing)
        this.listeners.add(animation, 'finish', () => {
            this.element.style.opacity = 1
        })
    }
    mount() {
        this.listeners = new Listeners()
        window.requestAnimationFrame(() => {
            if (this.element.getBoundingClientRect().top > window.innerHeight) {
                this.element.style.opacity = 0
                this.revealed = false
            }
            if (this.revealed) return this

            IntersectionManager.addById('scr-rvl-' + this.id, this.element, (entry, observer) => {
                if (entry.isIntersecting) {
                    entry.target.dispatchEvent(this.constructor.events.scrollReveal)
                    IntersectionManager.remove('scr-rvl-' + this.id)
                }
                }, {
                    root: this.observer.root,
                    rootMargin: this.observer.rootMargin,
                    threshold: this.observer.threshold
            })
            this.listeners.add(this.element, 'scrollReveal', () => {
                this.reveal()
            })
            return this
        })
    }

    destroy() {
        this.listeners.destroy()
        IntersectionManager.remove('scr-rvl-' + this.id)
    }
}
