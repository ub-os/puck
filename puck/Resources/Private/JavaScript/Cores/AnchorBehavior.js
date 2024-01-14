import { $, $$, $id, scrollTo } from '~/_htmc/Utility/DomUtility'
import Core from '~/_htmc/Core'

export default class AnchorBehavior extends Core {
    static attributes = {
        scrollOffset: 0,
        scrollTopOnCurrentLink: true,
    }

    isCurrentLink(el) {
        return !el.hash && (el.href === window.location.href || el.href === window.location.pathname)
    }

    isCurrentHashLink(el) {
        return el.hash?.substring(1).split('?')[0] && el.pathname === window.location.pathname
    }

    connect() {
        this.scrollOffset = this.scrollOffset || getComputedStyle(document.documentElement).getPropertyValue('--scroll-to-offset') || 0

        this.listeners.addDelegate(this.el, 'a', 'click', e => {
            if (this.isCurrentHashLink(e.delegateTarget)) {
                const id = e.delegateTarget.hash.substring(1).split('?')[0];
                if (!id) return
                const anchor = $id(id)
                if (!anchor) return
                e.preventDefault()
                const hashLinkEvent = new CustomEvent('hash-link-click', { detail: { linkElement: e.delegateTarget }})
                anchor.dispatchEvent(hashLinkEvent)
                if (anchor.hasAttribute('data-menu-anchor')) {
                    scrollTo(anchor.nextElementSibling, this.scrollOffset);
                } else {
                    scrollTo(anchor, this.scrollOffset);
                }
                history.replaceState(history.state, document.title, e.delegateTarget.href)
            } else if (this.scrollTopOnCurrentLink && this.isCurrentLink(e.delegateTarget)) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    left: 0,
                    behavior: 'smooth'
                });
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

