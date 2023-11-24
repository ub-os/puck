import { $, $$, jsx } from '~/General/Aliases'
import Listeners from "~/Classes/Listeners"
import ScrollSensitive from "~/Classes/Behaviors/ScrollSensitive";
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior"

export default class PageHeader extends AbstractBehavior {
    static props = {
        scrollTops: {},
        minScrollForHide: 300,
        stateClasses: {},
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
                this.lastScrollTop = document.documentElement.scrollTop || document.body.scrollTop
                this.ticking = false
                this.lastScrollDirection = scrollDirection
                if (this.lastState === newState) return
                this.lastState = newState
                if (scrollDirection === 'down' && this.lastScrollTop > this.minScrollForHide) {
                    this.el.classList.add(this.stateClasses.hidden)
                    this.el.classList.remove(this.stateClasses.visible)
                } else {
                    this.el.classList.add(this.stateClasses.visible)
                    this.el.classList.remove(this.stateClasses.hidden)
                }
            })
            this.ticking = true;
        }
    }
    mount() {
        this.stateClasses = {
            ...{
                hidden: '--scroll-down',
                visible: '--scroll-up',
            },
            ...this.stateClasses
        }
        this.scrollTops = {
            0: 1,
            ...this.scrollTops
        },
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
        this.scrollSensitive = new ScrollSensitive(this.el, { scrollTop: this.scrollTop }).mount()
        this.listeners = new Listeners()
        this.listeners.add(document.body, 'scrollTo', e => {
            this.paused = true
            this.el.classList.add(this.stateClasses.hidden)
            this.el.classList.remove(this.stateClasses.visible)
            setTimeout(() => {
                this.paused = false
            }, 1000)
        })
        this.listeners.add(window, 'scroll', this.scrollHandler.bind(this))
        return this
    }

    destroy() {
        this.listeners.destroy()
        this.scrollSensitive.destroy()
        this.el.classList.remove(this.stateClasses.hidden)
        this.el.classList.remove(this.stateClasses.visible)
    }
}
