import { $, $$, $id, scrollTo } from '~/_jcores/Utility/DomUtility'
import Core from '~/_jcores/Core'

export default class AnchorBehavior extends Core {
    static attributes = {
        scrollTopOnCurrentLink: true,
    }

    isInternalLink(el) {
        return el.origin === window.location.origin
    }

    isCurrentLink(el) {
        return this.isInternalLink(el) && el.pathname === window.location.pathname
    }

    isHashLink(el) {
        return el.hash ? true : false
    }

    getTargetFromHash(hash) {
        return $id(hash?.substring(1).split('?')[0])
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
            if (!this.isCurrentLink(e.delegateTarget)) return
            if (!this.isHashLink(e.delegateTarget)) {
                if (!this.scrollTopOnCurrentLink) return
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    left: 0,
                    behavior: 'smooth'
                });
                return
            }
            e.preventDefault()
            const anchorTarget = this.getTargetFromHash(e.delegateTarget.hash)
            if (!anchorTarget) return
            anchorTarget.dispatchEvent(
                new CustomEvent('hash-link-click', { detail: { linkElement: e.delegateTarget }})
            )
            this.scrollToTarget(anchorTarget)
            history.replaceState(history.state, document.title, e.delegateTarget.href)
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

