import {getNode} from '../General/Functions'


const createScrollRevealObserver = (
    {
        root = null,
        rootMargin = '0px 0px -40px 0px',
        threshold = 0
    } = {}) => {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target)
                entry.target.dispatchEvent(scrollRevealEvents.scrollReveal)
            }
        })
    }, {root, rootMargin, threshold})
    return observer
}
const scrollRevealEvents = {
    scrollReveal: new Event('scrollReveal'),
}
const scrollRevealObserver = createScrollRevealObserver()

class ScrollReveal {
    constructor(target, {
        preset = 'slide-up',
        presetTranslate = 10,
        animation = null,
        timing = {},
        observationTarget = null,
        observerOptions,
        ...options})
    {
        Object.assign(this, { preset, presetTranslate, animation, observerOptions, ...options })
        this.timing = {
            duration: 500,
            easing: 'ease-in-out',
            ...timing
        }
        this.node = getNode(target, 'ScrollReveal')
        this.observedNode = observationTarget ? getNode(observationTarget, 'ScrollReveal') : this.node
        this.revealed = true
        window.requestAnimationFrame(() => {
            if (this.observedNode.getBoundingClientRect().top > window.innerHeight) {
                this.node.style.opacity = 0
                this.revealed = false
            }
        })
        if (!this.animation) {
            this.animation = this.getAnimationPreset(this.preset)
        }
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
    getAnimationPreset(name) {
        switch (name) {
            case 'slide-up':
                return [
                    { transform: `translateY(${this.presetTranslate}px)`, opacity: 0 },
                    { transform: `translateY(0)`, opacity: 1 },
                ]
            case 'slide-down':
                return [
                    { transform: `translateY(-${this.presetTranslate}px)`, opacity: 0 },
                    { transform: `translateY(0)`, opacity: 1 },
                ]
            case 'slide-left':
                return [
                    { transform: `translateX(${this.presetTranslate}px)`, opacity: 0 },
                    { transform: `translateX(0)`, opacity: 1 },
                ]
            case 'slide-right':
                return [
                    { transform: `translateX(-${this.presetTranslate}px)`, opacity: 0 },
                    { transform: `translateX(0)`, opacity: 1 },
                ]
            default:
                return [
                    { opacity: 0 },
                    { opacity: 1 },
                ]
        }
    }
    mount() {
        if (this.observerOptions) {
            createScrollRevealObserver(this.observerOptions).observe(this.observedNode)
        } else {
            scrollRevealObserver.observe(this.observedNode)
        }
        this.observedNode.addEventListener('scrollReveal', () => {
            if (!this.revealed) this.reveal()
        })
        return this
    }
}

export {ScrollReveal, scrollRevealEvents, scrollRevealObserver}