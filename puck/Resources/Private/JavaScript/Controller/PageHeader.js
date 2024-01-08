import { $, $$, jsx } from '~/Utility/DomUtility'
import ListenerCollector from "~/Service/ListenerCollector.js"
import ScrollSensitive from "~/Controller/ScrollSensitive";
import Controller from "~/Application/Controller.js";

/**
 * @property {ScrollSensitive} scrollSensitiveController
 */
export default class PageHeader extends Controller {
    static attributes = {
        minScrollForHide: 100,
        downClass: '--scroll-down',
        upClass: '--scroll-up'
    }
    static injects = ['scroll-sensitive']
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
                    this.el.classList.add(this.downClass)
                    this.el.classList.remove(this.upClass)
                } else {
                    this.el.classList.add(this.upClass)
                    this.el.classList.remove(this.downClass)
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
        this.listeners.add(document.body, 'scrollTo', e => {
            this.paused = true
            this.el.classList.add(this.downClass)
            this.el.classList.remove(this.upClass)
            setTimeout(() => {
                this.paused = false
            }, 1000)
        })
        this.listeners.add(window, 'scroll', this.scrollHandler.bind(this))
        return this
    }

    disconnect() {
        this.listeners.destroy()
        this.el.classList.remove(this.downClass)
        this.el.classList.remove(this.upClass)
    }
}
