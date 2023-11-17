import { $, $$, jsx } from '../General/Aliases';
import ScrollSensitive from "../Classes/ScrollSensitive.js";
import AbstractComponent from "./AbstractComponent.js";
import Listeners from "./Listeners.js";

export default class PageHeader extends AbstractComponent {

    constructor(target, {
        scrollTops = {},
        minScrollForHide = 300,
        stateClasses = {},
        ...options}
    ) {
        super(target, options)
        Object.assign(this, {
            scrollTops: {
                0: 1,
                ...scrollTops
            },
            minScrollForHide,
            stateClasses: {
                hidden: '--scroll-down',
                visible: '--scroll-up',
                ...stateClasses
            }
        })
        this.lastScrollTop = 0
        this.ticking = false
        this.paused = false
        this.lastScrollDirection = 'down'
        this.lastState = 'visible'
        for (let breakpoint in this.scrollTops) {
            if (window.innerWidth > breakpoint) {
                this.scrollTop = this.scrollTops[breakpoint]
            }
        }
        this.pageHeaderScrollSensitive = new ScrollSensitive(this.element, { scrollTop: this.scrollTop }).mount()
    }
    checkScrollDirection() {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop
        const scrollDirection = currentScrollTop > this.lastScrollTop ? 'down' : 'up'
        return scrollDirection
    }

    scrollHandler() {
        if (!this.ticking && !this.paused) {
            window.requestAnimationFrame(() => {
                const scrollDirection = this.checkScrollDirection()
                const newState = scrollDirection === 'down' && this.lastScrollTop > this.minScrollForHide ? 'hidden' : 'visible'
                this.lastScrollTop = window.pageYOffset || document.documentElement.scrollTop
                this.ticking = false
                this.lastScrollDirection = scrollDirection
                if (this.lastState === newState) return
                this.lastState = newState
                if (scrollDirection === 'down' && this.lastScrollTop > this.minScrollForHide) {
                    this.element.classList.add(this.stateClasses.hidden)
                    this.element.classList.remove(this.stateClasses.visible)
                } else {
                    this.element.classList.add(this.stateClasses.visible)
                    this.element.classList.remove(this.stateClasses.hidden)
                }
            })
            this.ticking = true;
        }
    }
    mount() {
        this.listeners = new Listeners()
        this.listeners.add(document.body, 'scrollTo', e => {
            this.paused = true
            this.element.classList.add(this.stateClasses.hidden)
            this.element.classList.remove(this.stateClasses.visible)
            setTimeout(() => {
                this.paused = false
            }, 1000)
        })
        this.listeners.add(window, 'scroll', this.scrollHandler.bind(this))
        return this
    }

    destroy() {
        this.listeners.destroy()
        this.pageHeaderScrollSensitive.destroy()
        delete this.pageHeaderScrollSensitive
        this.element.classList.remove(this.stateClasses.hidden)
        this.element.classList.remove(this.stateClasses.visible)
    }
}