import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, $id, jsx, scrollTo } from '~/Utility/DomUtility'
import Logger from '~/Helper/Logger'
import { stim, htmx } from '~/setup'
import 'htmx-ext-head-support'
import 'htmx-ext-preload'

const on = (event, callback, options = {}) => document.documentElement.addEventListener(event, callback, options)
const colors = {
    'before': '#00ffd9',
    'after': '#00ffd9',
    'load': '#9efc90',
    'error': '#ef1a1a',
}
const htmxLifecycleEvents = {
    "htmx:beforeRequest": `color:${colors.before}`,
    "htmx:afterRequest": `color:${colors.after}`,
    "htmx:beforeSwap": `color:${colors.before}`,
    "htmx:oobBeforeSwap": `color:${colors.before}`,
    "htmx:afterSwap": `color:${colors.after}`,
    "htmx:load": `color:${colors.load}`,
    "htmx:beforeHistorySave": `color:${colors.before}`,
    "htmx:historyRestore": `color:${colors.after}`,
    "htmx:responseError": `color:${colors.error}`,
}


smoothscroll.polyfill()
stim.connect()
Logger.console.log(stim)

htmx.logger = (el, eventType, event) => {
    if (!htmxLifecycleEvents[eventType]) return
    let additional = isBodySwapEvent(event) ? '@root' :''
    Logger.console.log(`%c${eventType}${additional}`, htmxLifecycleEvents[eventType], event)
}
window.requestAnimationFrame(() => {
    $id('body').classList.remove('u-no-transition')
})
on("htmx:historyRestore", (event) => {
    $$('[data-render-excluded]').forEach(el => el.remove())
})

let focusAfterSwapSelector
const setFocusAfterSwapSelector = () => {
    focusAfterSwapSelector = document.activeElement
        .closest('[data-hx-focus-after-swap]')
        ?.getAttribute('data-hx-focus-after-swap')
}
const resolveFocusAfterSwap = () => {
    if (!focusAfterSwapSelector) return
    $(focusAfterSwapSelector)?.focus()
    focusAfterSwapSelector = null
}

on("htmx:beforeRequest", (event) => {
    setFocusAfterSwapSelector()
    if (event.target.tagName === "A" && event.target.pathname === window.location.pathname) {
        event.preventDefault()
        Logger.console.log('prevent htmx navigation to same page')
    }
})
on("htmx:responseError", (event) => {
    // route to error page
    window.location.href = event.detail.xhr.responseURL;
})
on("htmx:load", (event) => {
    if (isBodySwapEvent(event)) {
        //window.UC_UI?.restartCMP() // restart Usercentrics CMP UI if available
    } else {
        resolveFocusAfterSwap()
    }
    focusAfterSwapSelector = null
})
const isBodySwapEvent = event => {
    return event.target?.tagName === 'BODY' || event.target?.id === 'root' || event.elt?.id === 'root'
}