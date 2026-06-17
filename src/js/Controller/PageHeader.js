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

	//static aspects = ['scroll-sensitive']

	listeners = new ListenerRegistry()
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
				setTimeout(() => {
					this.ticking = false
				}, 40)
				const scrollDirection = this.checkScrollDirection(event)
				if (this.scrollDirection == scrollDirection) return
				this.setDirectionState(scrollDirection)
			})
			this.ticking = true
		}
	}

	setDirectionState(direction) {
		this.scrollDirection = direction
		const classes =
			this.scrollDirection === 'down'
				? { add: this.downClass, remove: this.upClass }
				: { add: this.upClass, remove: this.downClass }
		this.element.classList.add(classes.add)
		this.element.classList.remove(classes.remove)
		if (this.documentClassing) {
			document.documentElement.classList.add(`--${this.element.id}${classes.add}`)
			document.documentElement.classList.remove(`--${this.element.id}${classes.remove}`)
		}
	}

	pause() {
		this.pausedTimeOut = true
	}

	resume() {
		this.pausedTimeOut = null
	}

	connected() {
		this.listeners.add(
			window,
			'scroll',
			e => {
				if (document.documentElement.scrollTop < this.scrollTop) {
					this.element.classList.remove(this.scrollClass)
					if (this.documentClassing) {
						document.documentElement.classList.remove(`--${this.element.id}${this.scrollClass}`)
					}
				} else {
					this.element.classList.add(this.scrollClass)
					if (this.documentClassing) {
						document.documentElement.classList.add(`--${this.element.id}${this.scrollClass}`)
					}
				}
			},
			{ passive: true },
		)

		//this.listeners.add(window, 'keyup', this.scrollHandler.bind(this), {passive: true})
		//this.listeners.add(window, 'wheel', this.scrollHandler.bind(this), {passive: true})
		this.listeners.add(window, 'scroll', this.scrollHandler.bind(this), {
			passive: true,
		})
		this.stim.store.pageHeader = this
	}

	disconnected() {
		this.listeners.abort()
		this.element.classList.remove(this.downClass, this.upClass, this.scrollClass)
		this.stim.store.pageHeader = null
	}
}
