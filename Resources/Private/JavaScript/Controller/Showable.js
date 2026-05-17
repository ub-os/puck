import { Controller } from '@amdeu/stim'
import { ListenerRegistry } from 'Helper/ListenerRegistry'

export default class Showable extends Controller {
	static targets = ['control']
	static props = {
		active: false,
		groupId: '',
		duration: 0,
		trigger: 'click',
		activeClass: '--active',
		activatingClass: '--activating',
		deactivatingClass: '--deactivating',
		// only one showable in group can be active
		exclusiveGroup: true,
		// disable toggles
		disableToggles: false,
		// set state classes on documentElement
		documentClassing: true,
		// if url hash matches elements id on connect trigger show
		// clicking an anchor with hash matching elements id will trigger show
		urlHashShow: true,
		// remove matching hash from url on hide
		urlHashRemove: true,
		// focus on show
		focusOnShow: false,
		// hide on esc
		escHide: true,
		// hide on outside click
		outClickHide: false,
		// hide on self click
		selfClickHide: false,
		// hide on focusout
		focusOutHide: false,
		// hide on scroll
		scrollHide: false,
		// hide if matching element is clicked
		clickHideSelector: '',
		// target which is checked for outside click
		outTarget: null,
		// media is paused on hide
		pauseMediaOnHide: false,
		// iframes are reloaded on hide
		reloadIframeOnHide: false,
		// hide only works on the toggle that toggled on
		switchToggles: false,
		alwaysActive: false,
	}
	listeners = new ListenerRegistry()
	lastUsedToggle = null
	durationTimer = null
	/*** @property {Set<HTMLElement>} controlTargets */
	setClass(operation, className) {
		this.element.classList[operation](className)
		if (this.documentClassing) {
			document.documentElement.classList[operation](`--${this.element.id}-${this.identifier}${className}`)
		}
		this.controlTargets.forEach(t => {
			t.classList[operation](className)
		})
	}
	transitionClass(className) {
		if (this.duration > 0) {
			this.setClass('add', className)
			this.durationTimer = setTimeout(() => {
				this.setClass('remove', className)
				this.durationTimer = null
			}, this.duration)
		}
	}
	show({ transition = true, trigger = '' }) {
		if (this.groupEl) {
			this.groupEl.dispatchEvent(
				new CustomEvent(`${this.identifier}:toggle-group`, {
					detail: { showTarget: this.element },
				}),
			)
		}
		this.element.dispatchEvent(
			new CustomEvent(`${this.identifier}:show`, {
				detail: { transition, trigger },
			}),
		)
	}
	hide({ transition = true, changeUrlHash = true, trigger = '' } = {}) {
		this.element.dispatchEvent(
			new CustomEvent(`${this.identifier}:hide`, {
				detail: { transition, changeUrlHash, trigger },
			}),
		)
	}
	toggle({ transition = true, changeUrlHash = true, trigger = '' }, event = {}) {
		if (this.active && (!this.switchToggles || event.currentTarget !== this.lastUsedToggle)) {
			this.hide({ transition, changeUrlHash, trigger })
			this.lastUsedToggle = event.currentTarget
		} else if (!this.active) {
			this.show({ transition, trigger })
			this.lastUsedToggle = event.currentTarget
		}
	}
	onShow(event) {
		this.active = true
		this.setClass('add', this.activeClass)
		if (event.detail.transition) {
			this.transitionClass(this.activatingClass)
		} else {
			this.setClass('remove', this.activatingClass)
		}
		if (this.focusOnShow) {
			const focusTarget = this.element.querySelector('[data-autofocus]') || this.element
			focusTarget.focus()
		}
		return true
	}
	onHide(event) {
		this.active = false
		this.setClass('remove', this.activeClass)
		if (event.detail.transition) {
			this.transitionClass(this.deactivatingClass)
		} else {
			this.setClass('remove', this.deactivatingClass)
		}
		if (this.pauseMediaOnHide) {
			this.element.querySelectorAll('video, audio').forEach(item => {
				if (item.pause) item.pause()
			})
		}
		if (this.reloadIframeOnHide) {
			this.element.querySelectorAll('iframe').forEach(item => {
				if (item.src) {
					const src = item.src
					item.src = src
				}
			})
		}
		if (
			event.detail.changeUrlHash &&
			this.urlHashRemove &&
			window.location.hash.split('?')[0] === `#${this.element.id}`
		) {
			history.replaceState(history.state, document.title, location.href.replace(`#${this.element.id}`, '')) // remove hash from url
		}
		return true
	}

	controlTargetConnected(el) {
		el.ariaControls = this.element.id
		if (this.active) {
			el.classList.add(this.activeClass)
		}
		this.listeners.add(el, 'click', e => this.toggle({ trigger: 'control' }, e))
	}

	controlTargetDisconnected(el) {
		this.listeners.abortTarget(el)
	}

	connected() {
		this.groupEl = this.groupId ? document.getElementById(this.groupId) : null
		this.outEl = this.outTarget ? document.querySelector(this.outTarget) : this.element
		this.active
			? this.onShow({ detail: { transition: false } })
			: this.onHide({ detail: { transition: false, changeUrlHash: false } })

		this.listeners.add(this.element, `${this.identifier}:show`, event =>
			window.requestAnimationFrame(() => {
				if (this.active || event.defaultPrevented) return
				this.onShow(event)
			}),
		)
		this.listeners.add(this.element, `${this.identifier}:hide`, event =>
			window.requestAnimationFrame(() => {
				if (!this.active || event.defaultPrevented) return
				this.onHide(event)
			}),
		)

		if (this.groupEl) {
			this.listeners.add(this.groupEl, `${this.identifier}:toggle-group`, event => {
				if (event.defaultPrevented) return
				if (this.exclusiveGroup && this.active && !this.alwaysActive && event.detail.showTarget !== this.element) {
					this.hide({ trigger: 'exclusiveGroup' })
				}
			})
		}
		if (this.outClickHide) {
			this.listeners.add(document, 'click', event => {
				if (this.active && !this.outEl.contains(event.target)) this.hide({ trigger: 'outClick' })
			})
		}
		if (this.selfClickHide) {
			this.listeners.add(this.element, 'click', event => {
				if (this.active && event.target === this.element) this.hide({ trigger: 'selfClick' })
			})
		}
		if (this.focusOutHide) {
			this.listeners.add(this.outEl, 'focusout', event => {
				if (!this.active || this.outEl.contains(event.relatedTarget)) {
					return
				}
				this.hide({ trigger: 'focusOut' })
			})
		}
		if (this.escHide) {
			this.listeners.add(this.element, 'keydown', event => {
				if (event.key === 'Escape') this.hide({ trigger: 'esc' })
			})
		}
		if (this.scrollHide) {
			this.listeners.add(
				window,
				'scroll',
				() => {
					if (this.active) this.hide({ trigger: 'scroll' })
				},
				{ passive: true },
			)
		}
		if (this.clickHideSelector) {
			this.listeners.delegate(this.element, this.clickHideSelector, 'click', event => {
				if (this.active) this.hide({ trigger: `clickOnSelector:${this.clickHideSelector}` })
			})
		}
		if (this.urlHashShow) {
			if (window.location.hash.split('?')[0] === `#${this.element.id}`) this.show({ trigger: 'urlHash' })
			this.listeners.add(this.element, 'anchor-handler:hash-link-click', event => {
				this.lastUsedToggle = event.detail.linkElement
				this.show({ trigger: 'urlHash' })
			})
		}
	}
	disconnected() {
		this.setClass('remove', this.deactivatingClass)
		this.onHide({ detail: { transition: false, changeUrlHash: false } })
		this.listeners.abort()
	}
}