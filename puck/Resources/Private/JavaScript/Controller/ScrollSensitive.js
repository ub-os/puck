import { Controller } from '@oliveoilexpert/stim'
import { ListenerRegistry } from '~/Helper/ListenerRegistry.js'

export default class ScrollSensitive extends Controller {
	static props = {
		topInsideClass: '--top-inside-view',
		topAboveClass: '--top-above-view',
		topBelowClass: '--top-below-view',
		bottomInsideClass: '--bottom-inside-view',
		bottomAboveClass: '--bottom-above-view',
		bottomBelowClass: '--bottom-below-view',
		scrollClass: '--scroll',
		root: null,
		rootMargin: '0px 0px 0px 0px',
		threshold: [0, 1],
		target: null,
		cloneElementToRetainFlow: false,
		cloneClass: '-scroll-sense-clone',
		scrollTop: '',
	}

	listeners = new ListenerRegistry()

	observerCallback(entry, observer) {
		if (entry.rootBounds === null) return
		for (const side of ['top', 'bottom']) {
			if (entry.boundingClientRect[side] >= entry.rootBounds.top) {
				entry.target.classList.remove(this[`${side}AboveClass`])
				if (entry.boundingClientRect[side] <= entry.rootBounds.bottom) {
					entry.target.classList.add(this[`${side}InsideClass`])
					entry.target.classList.remove(this[`${side}BelowClass`])
				} else {
					entry.target.classList.add(this[`${side}BelowClass`])
					entry.target.classList.remove(this[`${side}InsideClass`])
				}
			} else {
				entry.target.classList.add(this[`${side}AboveClass`])
				entry.target.classList.remove(this[`${side}InsideClass`])
				entry.target.classList.remove(this[`${side}BelowClass`])
			}
		}
	}

	__correctScrollTop = null

	get correctScrollTop() {
		if (this.__correctScrollTop !== null) return this.__correctScrollTop
		let scrollTop = 0
		for (const topBreakpoint of this.scrollTop.toString().split(' ')) {
			const [top, breakpoint] = topBreakpoint.split('@')
			if (!breakpoint || window.innerWidth > Number.parseInt(breakpoint)) {
				scrollTop = Number.parseInt(top)
			}
		}
		return (this.__correctScrollTop = scrollTop)
	}

	scrollTopChanged() {
		this.__correctScrollTop = null
	}

	connected() {
		this.root = this.root ? document.querySelector(this.root) : null
		if (this.cloneElementToRetainFlow) {
			const clone = this.node.cloneNode(true)
			clone.classList.add(this.cloneClass)
			this.element.parentNode.insertBefore(clone, this.element)
			this.element = clone
		}
		if (this.scrollTop) {
			this.listeners.add(
				this.root || window,
				'scroll',
				e => {
					if ((this.root || document.documentElement).scrollTop < this.correctScrollTop) {
						this.element.classList.remove(this.scrollClass)
					} else {
						this.element.classList.add(this.scrollClass)
					}
				},
				{ passive: true },
			)
			this.resizeObserver = new ResizeObserver((entries, observer) => {
				this.__correctScrollTop = null
			})
			this.resizeObserver.observe(document.body)
		}
		this.intersectionObserver = new IntersectionObserver(
			(entries, observer) => {
				this.observerCallback(entries[0], observer)
			},
			{
				root: this.root,
				rootMargin: this.rootMargin,
				threshold: this.threshold,
			},
		)
		this.intersectionObserver.observe(this.target ? document.querySelector(this.target) : this.element)
	}

	disconnected() {
		this.listeners.abort()
		this.element.classList.remove(this.scrollClass)
		this.resizeObserver?.disconnect()
		this.intersectionObserver?.disconnect()
	}
}
