import { $, $$, $id, scrollTo } from '~/_jcores/Utility/DomUtility'
import Core from '~/_jcores/Core'

export default class AnchorBehavior extends Core {
    static attributes = {
        scrollTopOnCurrentLink: true,
    }

    isCurrentLink(el) {
        return !el.hash && (el.href === window.location.href || el.href === window.location.pathname)
    }

    isCurrentHashLink(el) {
        return el.hash?.substring(1).split('?')[0] && el.pathname === window.location.pathname
    }

    getTargetFromHash(hash) {
        const id = hash.substring(1).split('?')[0];
        if (!id) return
        return $id(id)
    }

    scrollToTarget(target) {
        if (target.hasAttribute('data-menu-anchor')) {
            target.nextElementSibling.scrollIntoView({ behavior: 'smooth', block: 'start' })
        } else {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }
    }

    connect() {
        this.listeners.addDelegate(this.el, 'a', 'click', e => {
            if (this.isCurrentHashLink(e.delegateTarget)) {
                const anchorTarget = this.getTargetFromHash(e.delegateTarget.hash)
                if (!anchorTarget) return
                e.preventDefault()
                anchorTarget.dispatchEvent(
                    new CustomEvent('hash-link-click', { detail: { linkElement: e.delegateTarget }})
                )
                this.scrollToTarget(anchorTarget)
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
            const anchorTarget = this.getTargetFromHash(window.location.hash)
            if (!anchorTarget) return
            this.scrollToTarget(anchorTarget)
        });
        return this
    }

    disconnect() {
        this.listeners.destroy()
    }
}

