import { $, $$, jsx } from '~/Utility/DomUtility'
import Listeners from "~/Service/Listeners"
import ScrollSensitive from "~/Controller/ScrollSensitive";
import Controller from "~/Application/Controller.js";

export default class PageHeader extends Controller {
    static props = {
        scrollTops: {
            0: 1
        },
        minScrollForHide: 100,
        classes: {
            hidden: '--scroll-down',
            visible: '--scroll-up',
        }
    }
    checkScrollDirection() {
        const currentScrollTop = document.documentElement.scrollTop || document.body.scrollTop
        const difference = Math.abs(currentScrollTop - this.lastScrollTop)
        if (difference < 5) return this.lastScrollDirection
        const scrollDirection = currentScrollTop > this.lastScrollTop ? 'down' : 'up'
        this.lastScrollTop = document.documentElement.scrollTop || document.body.scrollTop
        return scrollDirection
    }
    scrollHandler() {
        if (!this.ticking && !this.paused) {
            window.requestAnimationFrame(() => {
                const scrollDirection = this.checkScrollDirection()
                const newState = scrollDirection === 'down' && this.lastScrollTop > this.minScrollForHide ? 'hidden' : 'visible'
                this.ticking = false
                this.lastScrollDirection = scrollDirection
                if (this.lastState === newState) return
                this.lastState = newState
                if (scrollDirection === 'down' && this.lastScrollTop > this.minScrollForHide) {
                    this.el.classList.add(this.classes.hidden)
                    this.el.classList.remove(this.classes.visible)
                } else {
                    this.el.classList.add(this.classes.visible)
                    this.el.classList.remove(this.classes.hidden)
                }
            })
            this.ticking = true;
        }
    }
    lastScrollTop = 0
    ticking = false
    paused = false
    lastScrollDirection = 'up'
    lastState = 'visible'
    connect() {
        this.classes = {
            ...this.constructor.props.classes,
            ...this.classes
        }
        this.scrollTops = {
            ...this.constructor.props.scrollTops,
            ...this.scrollTops
        }
        for (let breakpoint in this.scrollTops) {
            if (window.innerWidth > breakpoint) {
                this.scrollTop = this.scrollTops[breakpoint]
            }
        }
        this.scrollSensitive = new ScrollSensitive(this.el, { scrollTop: this.scrollTop }).connect()
        this.listeners = new Listeners()
        this.listeners.add(document.body, 'scrollTo', e => {
            this.paused = true
            this.el.classList.add(this.classes.hidden)
            this.el.classList.remove(this.classes.visible)
            setTimeout(() => {
                this.paused = false
            }, 1000)
        })
        this.listeners.add(window, 'scroll', this.scrollHandler.bind(this))
        return this
    }

    disconnect() {
        this.listeners.destroy()
        this.scrollSensitive.disconnect()
        this.el.classList.remove(this.classes.hidden)
        this.el.classList.remove(this.classes.visible)
    }
}
