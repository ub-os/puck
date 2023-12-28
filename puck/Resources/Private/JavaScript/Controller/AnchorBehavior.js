import { $, $$, $id, scrollTo } from '~/Utility/DomUtility'
import Controller from '~/Application/Controller.js'
import ListenerCollector from '~/Service/ListenerCollector.js'

export default class AnchorBehavior extends Controller {
    static props = {
        scrollOffset: 0,
        scrollTopOnCurrentLink: true,
        localClass: '-local',
        externalClass: '-external',
    }

    isCurrentLink(el) {
        return !el.hash && (el.href === window.location.href || el.href === window.location.pathname || el.to === window.location.href || el.to === window.location.pathname)
    }

    isCurrentHashLink(el) {
        return el.hash?.substring(1).split('?')[0] && el.pathname === window.location.pathname
    }

    connect() {
        this.scrollOffset = this.scrollOffset || getComputedStyle(document.documentElement).getPropertyValue('--scroll-to-offset') || 0
        this.el.$$('a').forEach(el => {
            const isCurrentLink = this.isCurrentLink(el);
            const isCurrentHashLink = this.isCurrentHashLink(el);
            if (this.localClass && el.hostname === window.location.hostname) {
                el.classList.add(this.localClass);
            }
            if (this.externalClass && el.hostname !== window.location.hostname) {
                el.classList.add(this.externalClass);
            }
            if (el.getAttribute('data-action')) return
            if (this.scrollTopOnCurrentLink && isCurrentLink) {
                this.listeners.add(el, 'click', e => {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        left: 0,
                        behavior: 'smooth'
                    });
                })
                return
            }
            if (isCurrentHashLink) {
                const id = el.hash.substring(1).split('?')[0];
                if (!id) return
                const anchor = $id(id)
                if (!anchor) return
                this.listeners.add(el, 'click', e => {
                    e.preventDefault()
                    const hashLinkEvent = new Event('hash-link-clicked')
                    hashLinkEvent.detail = { linkElement: el }
                    anchor.dispatchEvent(hashLinkEvent)
                    if (anchor.hasAttribute('data-menu-anchor')) {
                        scrollTo(anchor.nextElementSibling, this.scrollOffset);
                    } else {
                        scrollTo(anchor, this.scrollOffset);
                    }
                    history.replaceState(history.state, document.title, el.href)
                })
            }
        })
        // scroll to hash id on page load
        window.requestAnimationFrame(() => {
            const id = window.location.hash.substring(1).split('?')[0];
            if (id) scrollTo(id, this.scrollOffset);
        });
        return this
    }

    disconnect() {
        this.listeners.destroy()
    }
}

