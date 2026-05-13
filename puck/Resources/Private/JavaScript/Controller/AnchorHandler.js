import { Controller } from '@amdeu/stim'
import { ListenerRegistry } from '~/Helper/ListenerRegistry'
import { tryViewTransition } from "~/Utility/DomUtility";

export default class AnchorHandler extends Controller {
	static props = {
		scrollTopOnCurrentLink: true,
	}
	static targets = ['menuAnchor']

	listeners = new ListenerRegistry()
	isDragging = false
	dragDelta = 6

	menuAnchorTargetConnected(el) {
		this.scrollTargetIntersectionObserver.observe(el.nextElementSibling)
		el.nextElementSibling.setAttribute('data-menu-anchor-id', el.id)
	}

	isCurrentLink(el) {
		return el.origin === window.location.origin && el.pathname === window.location.pathname
	}

	isHashLink(el) {
		return Boolean(el.hash)
	}

	getTargetFromHash(hash) {
		const id = hash?.substring(1).split('?')[0]
		return id ? document.getElementById(id) : null
	}

	scrollToTarget(target, { behavior = 'smooth', block = 'start' } = {}) {

		this.stim.store.pageHeader?.setDirectionState('down')
		this.stim.store.pageHeader?.pause()
		this.listeners.add(window, 'scrollend', () => {
			this.stim.store.pageHeader?.resume()
		}, { once: true })

		if (target.classList.contains('e-anchor')) {
			target.nextElementSibling.scrollIntoView({ behavior, block })
		} else {
			target.scrollIntoView({ behavior, block })
		}
	}

	trackDragState() {
		// track if user is dragging to prevent click events
		let startX
		let startY
		const mouseDown = event => {
			this.isDragging = false
			startX = event.pageX
			startY = event.pageY
		}
		const mouseUp = event => {
			const diffX = Math.abs(event.pageX - startX)
			const diffY = Math.abs(event.pageY - startY)
			this.isDragging = diffX >= this.dragDelta || diffY >= this.dragDelta
		}
		this.listeners.add(document.body, 'mousedown', mouseDown)
		this.listeners.add(document.body, 'mouseup', mouseUp)
	}

	handleOnPageAnchors() {
		// handle clicks for on-page anchor links
		// ignore links with data-fetch attribute
		// ignore links that are not current page links
		// ignore dragging clicks
		// scroll to top for non-hash current page links

		// observe intersection of menu anchors (or their next sibling) so we can give classes to the links targeting the menu anchors
		this.scrollTargetIntersectionObserver = new IntersectionObserver(entries => {
			entries.forEach(entry => {
				const id = entry.target.getAttribute('data-menu-anchor-id') || entry.target.id
				const anchorLinks = document.querySelectorAll(`a[href="#${id}"]`)
				if (entry.isIntersecting) {
					anchorLinks.forEach(anchor => {
						anchor.classList.add('--visibleTarget')
					})
				} else {
					anchorLinks.forEach(anchor => {
						anchor.classList.remove('--visibleTarget')
					})
				}
			})
		}, {
			rootMargin: '-200px 0px -200px 0px',
		})

		this.listeners.delegate(document.body, 'a:not([data-fetch])', 'click', e => {
			if (this.isDragging) return
			if (e.delegateTarget.getAttribute('href') === '#') return
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
	}

	handleLinkAreas() {
		// handle clicks for link areas, triggering the first link inside
		// ignore clicks on actual links inside link areas
		// ignore right clicks and dragging
		// open in new tab if meta or middle click
		this.listeners.delegate(document.body, '[data-link-area]', 'click', e => {
			if (e.target.tagName === 'A' || this.isDragging || e.altKey) return
			const link = e.delegateTarget.querySelector('a')
			if (!link) return
			if (e.metaKey || e.ctrlKey || e.shiftKey) {
				window.open(link.href, '_blank')
				return
			}
			link.click()
		})
		this.listeners.delegate(document.body, '[data-link-area]', 'auxclick', e => {
			if (e.target.tagName === 'A' || this.isDragging || e.button > 1) return
			const link = e.delegateTarget.querySelector('a')
			if (!link) return
			window.open(link.href, '_blank')
		})
	}

	// handleFetchLinks() {
	// 	// handle clicks for fetch links (alternative to htmx content swapping)
	// 	// ignore clicks with certain modifier keys or dragging, to allow open in new tab etc.
	// 	// show loading state on link and target element
	// 	// fetch content from data-fetch url or href
	// 	// replace (or append/prepend) content of data-target element with fetched content sub-element selected by data-select or data-target id
	// 	// update browser url with data-push-url or data-replace-url if set
	// 	// use view transitions if available
	// 	this.listeners.delegate(document.body, '[data-fetch]', 'click', async (e) => {
	// 		const el = e.delegateTarget
	//
	// 		if (this.isDragging || e.shiftKey || e.metaKey || e.ctrlKey) return
	// 		if (el.href) {
	// 			e.preventDefault()
	// 		}
	//
	// 		const fetchUrl = el.getAttribute('data-fetch') || el.href
	// 		const targetId = el.getAttribute('data-target')
	// 		const selectId = el.getAttribute('data-select') || targetId
	// 		const target = targetId ? document.getElementById(targetId) : el
	// 		const mode = el.getAttribute('data-mode') || 'replace'
	// 		const replaceUrl = el.hasAttribute('data-replace-url')
	// 			? (el.getAttribute('data-replace-url') || el.href)
	// 			: null
	// 		const pushUrl = el.hasAttribute('data-push-url')
	// 			? (el.getAttribute('data-push-url') || el.href)
	// 			: null
	//
	// 		if (!target || !fetchUrl) return
	//
	// 		el.classList.add('--fetchLoading')
	// 		target.classList.add('--fetchLoading')
	// 		try {
	// 			const response = await fetch(fetchUrl)
	// 			if (!response.ok) throw new Error(`HTTP ${response.status}`)
	//
	// 			const html = await response.text()
	// 			const content = (new DOMParser()).parseFromString(html, 'text/html').getElementById(selectId)
	// 			if (!content) {
	// 				console.error(`Selected element #${selectId} not found`)
	// 				return;
	// 			}
	// 			const clone = content.cloneNode(true)
	// 			tryViewTransition(() => {
	// 				if (mode === 'replace') {
	// 					target.replaceWith(clone)
	// 				} else if (mode === 'append') {
	// 					target.append(...clone.childNodes)
	// 				} else if (mode === 'prepend') {
	// 					target.prepend(...clone.childNodes)
	// 				}
	// 			})
	// 			if (pushUrl) {
	// 				window.history.pushState(window.history.state, '', pushUrl)
	// 			}
	// 			if (replaceUrl) {
	// 				window.history.replaceState(window.history.state, '', replaceUrl)
	// 			}
	//
	// 			if (clone.hasAttribute('data-focus-after-swap')) {
	// 				clone.querySelector(clone.getAttribute('data-focus-after-swap'))?.focus()
	// 			}
	//
	// 		} catch (error) {
	// 			console.error('Fetch error:', error)
	// 			if (el.href) window.location.href = el.href
	// 		} finally {
	// 			el.classList.remove('--fetchLoading')
	// 			target.classList.remove('--fetchLoading')
	// 		}
	// 	})
	// }

	connected() {
		this.trackDragState()
		this.handleOnPageAnchors()
		this.handleLinkAreas()
		// this.handleFetchLinks()

		// scroll to hash id on page load
		window.requestAnimationFrame(() => {
			const anchorTarget = this.getTargetFromHash(window.location.hash)
			if (!anchorTarget) return
			this.scrollToTarget(anchorTarget)
		})
	}

	disconnected() {
		this.listeners.abort()
	}
}
