import Logger from '#/Helper/Logger.js'
import { htmx, stim } from '#/setup.js'

const colors = {
	before: '#00ffd9',
	after: '#9efc90',
	load: '#9efc90',
	error: '#ef1a1a',
}
const htmxLifecycleEvents = {
	'htmx:before:request': `color:${colors.before}`,
	'htmx:after:request': `color:${colors.after}`,
	'htmx:before:swap': `color:${colors.before}`,
	'htmx:after:swap': `color:${colors.after}`,
	'htmx:before:history:update': `color:${colors.before}`,
	'htmx:before:history:restore': `color:${colors.after}`,
	'htmx:response:error': `color:${colors.error}`,
}

stim.connect()
Logger.console.log({
	htmx,
	stim,
})
window.puckExeLoaded = true

Object.entries(htmxLifecycleEvents).forEach(([eventType, style]) => {
	htmx.on(eventType, event => {
		const additional = isBodySwapEvent(event) ? '@root' : ''
		Logger.console.log(`%c${eventType}${additional}`, style, event)
	})
})

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

htmx.on('htmx:before:request', event => {
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

htmx.on('htmx:response:error', event => {
	// navigate to the error response so the server-rendered error page is shown;
	// fall back to reloading the current page when there is no usable URL (e.g. network error)
	const url = event.detail?.xhr?.responseURL
	window.location.href = url && url !== '' ? url : window.location.href
})

htmx.on('htmx:after:init', event => {
	if (!isBodySwapEvent(event)) {
		resolveFocusAfterSwap()
	}
	focusAfterSwapSelector = null
})

// current solution for usercentrics not re-initializing UI after js navigation
// htmx.on('htmx:after:init', event => {
// 	if (isBodySwapEvent(event)) {
// 		if (window.__ucCmp) {
// 			if (window.__ucCmp.loadCmpView) {
// 				window.__ucCmp.loadCmpView()
// 			}
// 		}
// 	}
// })
// window.addEventListener('UC_UI_INITIALIZED', event => {
// 	window.addEventListener('ucEvent', e => {
// 		if (e.detail.event == 'consent_status' && e.detail.type == 'EXPLICIT') {
// 			window.__ucCmp.cmpController.ui.initialView = 'none'
// 		}
// 	})
// })

htmx.on('htmx:history:cache:before:save', event => {
	Logger.console.log('cleanup document for history cache', event)
	event.detail.target.querySelectorAll('[data-hx-history-excluded]').forEach(el => {
		el.remove()
	})
})

htmx.on('htmx:before:head:remove', event => {
	// prevent removing head elements with data-hx-preserve attribute
	if (event.detail.headElement.hasAttribute('data-hx-preserve')) {
		event.preventDefault();
	}
	if (event.detail.headElement.id === 'uc-block-styles') {
		event.preventDefault();
	}
})
htmx.on('htmx:before:head:add', event => {
	const el = event.detail.headElement
	// if the element has an id and a data-hx-preserve attribute, and an element with that id already exists, prevent adding the element (prevents duplicate head elements that only differ in cache-busting query params)
	// @todo: doesn't work anymore, hx-head always merges defer scripts :((
	if (el.id && el.getAttribute('data-hx-preserve')) {
		Logger.console.log('prevented adding head element with id', el.id)
		event.preventDefault();
	}
})

const isBodySwapEvent = event => {
	return event.target?.tagName === 'BODY' || event.target?.id === 'root' || event.elt?.id === 'root'
}