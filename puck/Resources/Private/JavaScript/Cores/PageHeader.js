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
        upClass: '--scroll-up',
        documentClassing: true
    }

    static injects = ['scroll-sensitive']

    lastScrollTop = 0
    scrollDirection = ''
    ticking = false
    pausedTimeOut = null

    checkScrollDirection() {
        const currentScrollTop = document.documentElement.scrollTop || document.body.scrollTop
        const difference = Math.abs(currentScrollTop - this.lastScrollTop)
        if (difference < 2) return this.scrollDirection
        const scrollDirection = currentScrollTop > this.lastScrollTop ? 'down' : 'up'
        this.lastScrollTop = document.documentElement.scrollTop || document.body.scrollTop
        return scrollDirection
    }

    scrollHandler(event) {
        if (!this.ticking && this.pausedTimeOut == null) {
            window.requestAnimationFrame(() => {
/*                setTimeout(() => {
                    this.ticking = false
                }, 50)*/
                const scrollDirection = this.checkScrollDirection(event)
                if (this.scrollDirection == scrollDirection) return
                this.scrollDirection = scrollDirection
                const classes = this.scrollDirection === 'down' ? {add: this.downClass, remove: this.upClass} : {add: this.upClass, remove: this.downClass}
                this.el.classList.add(classes.add)
                this.el.classList.remove(classes.remove)
                if (this.documentClassing) {
                    document.documentElement.classList.add(`--${this.el.id}${classes.add}`)
                    document.documentElement.classList.remove(`--${this.el.id}${classes.remove}`)
                }
            })
            //this.ticking = true;
        }
    }

    connect() {
        this.listeners.add(window, 'scroll', e => {
            if (document.documentElement.scrollTop < this.scrollTop) {
                this.el.classList.remove(this.scrollClass)
                if (this.documentClassing) {
                    document.documentElement.classList.remove(`--${this.el.id}${this.scrollClass}`)
                }
            } else {
                this.el.classList.add(this.scrollClass)
                if (this.documentClassing) {
                    document.documentElement.classList.add(`--${this.el.id}${this.scrollClass}`)
                }
            }
        }, {passive: true})

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
