import { Controller } from '@amdeu/stim'
import { ListenerRegistry } from '#/Helper/ListenerRegistry.js'

export default class PageHeader extends Controller {
	static props = {
		scrollTop: 60,
		scrollClass: '--scroll',
		downClass: '--scrollDown',
		upClass: '--scrollUp',
		documentClassing: true,
	}

	listeners = new ListenerRegistry()
	lastScrollTop = 0
	scrollDirection = ''
	ticking = false
	paused = null
	directionUpdatesPaused = null
	focusWithin = false

	getScrollTop() {
		return document.documentElement.scrollTop || document.body.scrollTop
	}

	toggleClasses(add, remove) {
		this.element.classList.add(add)
		this.element.classList.remove(remove)
		if (this.documentClassing && this.element.id) {
			document.documentElement.classList.add(`--${this.element.id}${add}`)
			document.documentElement.classList.remove(`--${this.element.id}${remove}`)
		}
	}

	checkScrollDirection(currentScrollTop) {
		const difference = Math.abs(currentScrollTop - this.lastScrollTop)
		if (difference < 15) return this.scrollDirection
		const scrollDirection = currentScrollTop > this.lastScrollTop ? 'down' : 'up'
		this.lastScrollTop = currentScrollTop
		return scrollDirection
	}

	scrollHandler() {
		if (this.ticking || this.paused != null) return
		this.ticking = true

		window.requestAnimationFrame(() => {
			const currentScrollTop = this.getScrollTop()

			// scrollClass toggle (height-based, not direction-based)
			this.element.classList.toggle(this.scrollClass, currentScrollTop >= this.scrollTop)
			if (this.documentClassing && this.element.id) {
				document.documentElement.classList.toggle(
					`--${this.element.id}${this.scrollClass}`,
					currentScrollTop >= this.scrollTop,
				)
			}
			if (!this.directionUpdatesPaused) {
				const scrollDirection = this.checkScrollDirection(currentScrollTop)
				const shouldUpdate =
					scrollDirection !== this.scrollDirection &&
					!(this.focusWithin && scrollDirection === 'down')

				if (shouldUpdate) this.setDirectionState(scrollDirection)
			}
			this.ticking = false
		})
	}

	setDirectionState(direction) {
		this.scrollDirection = direction
		const classes =
			direction === 'down'
				? { add: this.downClass, remove: this.upClass }
				: { add: this.upClass, remove: this.downClass }
		this.toggleClasses(classes.add, classes.remove)
	}

	handleFocusIn() {
		this.focusWithin = true
		if (this.scrollDirection !== 'up') this.setDirectionState('up')
	}

	handleFocusOut() {
		window.requestAnimationFrame(() => {
			if (!this.element.contains(document.activeElement)) {
				this.focusWithin = false
			}
		})
	}

	pause() {
		this.paused = true
	}

	resume() {
		this.paused = null
	}

	pauseDirectionUpdate() {
		this.directionUpdatesPaused = true
	}

	resumeDirectionUpdate() {
		this.directionUpdatesPaused = null
	}

	connected() {
		this.listeners.add(window, 'scroll', this.scrollHandler.bind(this), { passive: true })
		this.listeners.add(this.element, 'focusin', this.handleFocusIn.bind(this))
		this.listeners.add(this.element, 'focusout', this.handleFocusOut.bind(this))
		this.stim.store.pageHeader = this
	}

	disconnected() {
		this.listeners.abort()
		this.element.classList.remove(this.downClass, this.upClass, this.scrollClass)
		this.stim.store.pageHeader = null
	}
}