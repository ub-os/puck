import { ElementTrait } from '~/stim2'
import { EventListenerRegistry } from '~/Helper/EventListener.js'
import { $, $$, $id, scrollTo } from '~/Utility/DomUtility'

export default class AnchorScrolling extends ElementTrait {
	static props = {
		scrollTopOnCurrentLink: true,
	}
	listeners = new EventListenerRegistry()
	isCurrentLink(el) {
		return (
			el.origin === window.location.origin &&
			el.pathname === window.location.pathname
		)
	}

	isHashLink(el) {
		return Boolean(el.hash)
	}

	getTargetFromHash(hash) {
		return $id(hash?.substring(1).split('?')[0])
	}

	scrollToTarget(target, { behavior = 'smooth', block = 'start' } = {}) {
		if (target.hasAttribute('data-menu-anchor')) {
			target.nextElementSibling.scrollIntoView({ behavior, block })
		} else {
			target.scrollIntoView({ behavior, block })
		}
	}

	connected() {
		this.listeners.addDelegate(this.el, 'a', 'click', e => {
			if (!this.isCurrentLink(e.delegateTarget)) return
			if (!this.isHashLink(e.delegateTarget)) {
				if (!this.scrollTopOnCurrentLink) return
				e.preventDefault()
				window.scrollTo({
					top: 0,
					left: 0,
					behavior: 'smooth',
				})
				return
			}
			e.preventDefault()
			const anchorTarget = this.getTargetFromHash(e.delegateTarget.hash)
			if (!anchorTarget) return
			anchorTarget.dispatchEvent(
				new CustomEvent('hash-link-click', {
					detail: { linkElement: e.delegateTarget },
				}),
			)
			this.scrollToTarget(anchorTarget)
			history.replaceState(history.state, document.title, e.delegateTarget.href)
		})

		// scroll to hash id on page load
		window.requestAnimationFrame(() => {
			const anchorTarget = this.getTargetFromHash(window.location.hash)
			if (!anchorTarget) return
			this.scrollToTarget(anchorTarget)
		})
		return this
	}

	disconnected() {
		this.listeners.clear()
	}
}
