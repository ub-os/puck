import { Controller } from '~/stim2'
import { EventListenerSet } from '~/Helper/EventListener.js'

export default class AnchorHandler extends Controller {
	static props = {
		scrollTopOnCurrentLink: true,
	}
	listeners = new EventListenerSet()
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
		const id = hash?.substring(1).split('?')[0]
		return id ? document.getElementById(id) : null
	}

	scrollToTarget(target, { behavior = 'smooth', block = 'start' } = {}) {
		if (target.hasAttribute('data-menu-anchor')) {
			target.nextElementSibling.scrollIntoView({ behavior, block })
		} else {
			target.scrollIntoView({ behavior, block })
		}
	}

	connected() {
		this.listeners.addDelegate(document.body, 'a', 'click', e => {
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
				new CustomEvent(`${this.identifier}:hash-link-click`, {
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
	}

	disconnected() {
		this.listeners.clear()
	}
}
