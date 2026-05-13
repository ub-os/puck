import Logger from '~/Helper/Logger'
import { htmx, stim } from '~/setup'
import 'htmx-ext-head-support'
import 'htmx-ext-preload'

const on = (event, callback, options = {}) => document.documentElement.addEventListener(event, callback, options)
const colors = {
	before: '#00ffd9',
	after: '#00ffd9',
	load: '#9efc90',
	error: '#ef1a1a',
}
const htmxLifecycleEvents = {
	'htmx:beforeRequest': `color:${colors.before}`,
	'htmx:afterRequest': `color:${colors.after}`,
	'htmx:beforeSwap': `color:${colors.before}`,
	'htmx:oobBeforeSwap': `color:${colors.before}`,
	'htmx:afterSwap': `color:${colors.after}`,
	'htmx:load': `color:${colors.load}`,
	'htmx:beforeHistorySave': `color:${colors.before}`,
	'htmx:historyRestore': `color:${colors.after}`,
	'htmx:responseError': `color:${colors.error}`,
}

stim.connect()
Logger.console.log(stim)

// htmx event logging
htmx.logger = (el, eventType, event) => {
	if (!htmxLifecycleEvents[eventType]) return
	const additional = isBodySwapEvent(event) ? '@root' : ''
	Logger.console.log(`%c${eventType}${additional}`, htmxLifecycleEvents[eventType], event)
}
window.requestAnimationFrame(() => {
	document.body.classList.remove('u-transition-off')
})

// resolve focus after content swap
let focusAfterSwapSelector
const setFocusAfterSwapSelector = () => {
	focusAfterSwapSelector = document.activeElement
		.closest('[data-hx-focus-after-swap]')
		?.getAttribute('data-hx-focus-after-swap')
}
const resolveFocusAfterSwap = () => {
	if (!focusAfterSwapSelector) return
	document.querySelector(focusAfterSwapSelector)?.focus()
	focusAfterSwapSelector = null
}

on('htmx:beforeRequest', event => {
	setFocusAfterSwapSelector()
	if (
		event.target.tagName === 'A' &&
		event.target.pathname === window.location.pathname &&
		event.target.search === window.location.search
	) {
		event.preventDefault()
		Logger.console.log('prevent htmx navigation to same page')
	}
})

on('htmx:responseError', event => {
	// route to error page
	window.location.href = event.detail.xhr.responseURL
})
on('htmx:load', event => {
	if (isBodySwapEvent(event)) {
		// if (window.__ucCmp) {
		// 	window.__ucCmp.loadCmpView()
		// 	window.__ucCmp.cmpController.ui.initialView = 'none'
		// }
	} else {
		resolveFocusAfterSwap()
	}
	focusAfterSwapSelector = null
})

on("htmx:historyRestore", (event) => {
	// after swapping from htmx history cache, clean up elements that should be excluded
	// could be done on htmx:beforeHistorySave, but removing elements at that point would be visible, as history save happens before the page content is swapped out
	// if view transitions are enabled for history restores, we need to wait for the swap to finish before removing elements
	if (htmx.config.globalViewTransitions) {
		on('htmx:afterSwap', () => {
			document.querySelectorAll('[data-hx-history-excluded]').forEach(el => {
				el.remove()
			})
			console.log('removed hx history excluded elements after history restore')
		}, { once: true })
	} else {
		document.querySelectorAll('[data-hx-history-excluded]').forEach(el => {
			el.remove()
		})
		console.log('removed hx history excluded elements after history restore')
	}
})

on('htmx:removingHeadElement', event => {
	// prevent removing head elements with data-hx-preserve attribute
	if (event.detail.headElement.hasAttribute('data-hx-preserve')) {
		event.preventDefault();
	}
	if (event.detail.headElement.id === 'uc-block-styles') {
		event.preventDefault();
	}
})
on('htmx:addingHeadElement', event => {
	const el = event.detail.headElement.querySelector('*')
	// if the element has an id and a data-hx-preserve attribute, and an element with that id already exists, prevent adding the element (prevents duplicate head elements that only differ in cache-busting query params)
	if (el.id && el.getAttribute('data-hx-preserve') && document.getElementById(el.id)) {
		event.preventDefault();
	}
})

const isBodySwapEvent = event => {
	return event.target?.tagName === 'BODY' || event.target?.id === 'root' || event.elt?.id === 'root'
}
