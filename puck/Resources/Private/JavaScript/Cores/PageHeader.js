import { $, $$, jsx } from '~/_jcores/Utility/DomUtility'
import ScrollSensitive from "~/Cores/ScrollSensitive";
import Core from "~/_jcores/Core"

/**
 * @property {ScrollSensitive} scrollSensitiveCore
 */
export default class PageHeader extends Core {
    static attributes = {
        scrollTop: 100,
        scrollClass: '--scroll',
        downClass: '--scroll-down',
        upClass: '--scroll-up'
    }

    static injects = ['scroll-sensitive']

    lastScrollTop = 0
    scrollDirection = ''
    ticking = false
    pausedTimeOut = null

    checkScrollDirection() {
        const currentScrollTop = document.documentElement.scrollTop || document.body.scrollTop
        const difference = Math.abs(currentScrollTop - this.lastScrollTop)
        if (difference < 10) return this.scrollDirection
        const scrollDirection = currentScrollTop > this.lastScrollTop ? 'down' : 'up'
        this.lastScrollTop = document.documentElement.scrollTop || document.body.scrollTop
        return scrollDirection
    }

    scrollHandler(event) {
        if (!this.ticking && this.pausedTimeOut == null) {
            window.requestAnimationFrame(() => {
                setTimeout(() => {
                    this.ticking = false
                }, 200)
                const scrollDirection = this.checkScrollDirection(event)
                if (this.scrollDirection == scrollDirection) return
                this.scrollDirection = scrollDirection
                if (this.scrollDirection === 'down') {
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

    connect() {
        this.listeners.add(window, 'scroll', e => {
            if (document.documentElement.scrollTop < this.scrollTop) {
                this.el.classList.remove(this.scrollClass)
            } else {
                this.el.classList.add(this.scrollClass)
            }
        })

        this.listeners.add(window, 'DOMMouseScroll', this.scrollHandler.bind(this))
        this.listeners.add(window, 'keyup', this.scrollHandler.bind(this))
        this.listeners.add(window, 'mousewheel', this.scrollHandler.bind(this))
        return this
    }

    disconnect() {
        this.listeners.destroy()
        this.el.classList.remove(this.downClass)
        this.el.classList.remove(this.upClass)
        this.el.classList.remove(this.scrollClass)
    }
}
