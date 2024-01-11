import htmx from 'htmx.org'
import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, $id, jsx, scrollTo } from '~/_Stim/Utility/DomUtility'
import Logger from '~/Service/Logger'
import { ObserverCollector } from '~/Service/ObserverCollector'
import App from '~/_Stim/Application'
import '~/app.js'

smoothscroll.polyfill()

window.htmx = htmx
htmx.config.scrollBehavior = 'auto'
htmx.config.defaultSwapStyle = 'outerHTML'
htmx.config.defaultSwapDelay = 0
htmx.config.defaultSettleDelay = 0
htmx.config.globalViewTransitions = true

const doc = document.documentElement
let isInitialLoad = true
let isMounted = false

// fix for htmx initializing on DOMContentLoaded or if document.readyState is 'complete'
// because in this setup DOMContentLoaded is usually fired BEFORE readyState is 'complete'
let domContentLoaded = document.readyState === 'complete'
document.addEventListener('DOMContentLoaded', () => domContentLoaded = true)
document.addEventListener('readystatechange', e => {
    if (domContentLoaded || document.readyState !== 'complete') return
    document.dispatchEvent(new Event('DOMContentLoaded'))
})


const mountBody = () => {
    if (isMounted) return
    App.connect()
    window.requestAnimationFrame(() => {
        $id('root').classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => element.classList.remove('u-initially-hidden'))
        document.body.dispatchEvent(new CustomEvent('toggle-off-all', {detail: {transition: false}}))
    })
    Logger.console.log(`%capplication:mount`, "color:orange")
    Logger.console.log(App)
    isMounted = true
}

const clearBody = () => {
    if (!isMounted) return
    Logger.console.time('body cleanup')
    $$('[data-render-excluded]').forEach(el => el.remove())
    $$('.--scroll').forEach(el => el.classList.remove('--scroll'))
    App.disconnect()
    ObserverCollector.instance.clear()
    Logger.console.log(`%capplication:clear`, "color:orange")
    Logger.console.timeEnd('body cleanup')
    isMounted = false
}

const isBodyEvent = event => {
    return event.detail.boosted || event.detail.elt.tagName === 'BODY' || event.detail.elt.hasAttribute('data-hx-boost-root')
}
const logHtmxLifecycleEvent = (event, eventTypeSuffix = '') => {
    Logger.console.log(`%c${event.type}${eventTypeSuffix}`, "color:lightgreen", event)
}

doc.addEventListener("htmx:beforeRequest", (event) => {
    if (isBodyEvent(event)) {
        if (event.detail.pathInfo.requestPath === window.location.href) {
            event.preventDefault()
            return
        }
        logHtmxLifecycleEvent(event, ':body')
    } else {
        logHtmxLifecycleEvent(event)
    }
})
doc.addEventListener("htmx:beforeSwap", (event) => {
    if (isBodyEvent(event)) {
        logHtmxLifecycleEvent(event, ':body')
    } else {
        logHtmxLifecycleEvent(event)
    }
    if (event.target.hasAttribute('data-hx-target-scroll')) {
        scrollTo(event.target)
    }
})
doc.addEventListener("htmx:oobBeforeSwap", (event) => {
    logHtmxLifecycleEvent(event)
})
doc.addEventListener("htmx:afterSwap", (event) => {
    if (isBodyEvent(event)) {


        clearBody()
    } else {
        logHtmxLifecycleEvent(event)
    }
})
doc.addEventListener("htmx:load", (event) => {
    if (isInitialLoad) {
        isInitialLoad = false
        logHtmxLifecycleEvent(event, ':initial')
        Logger.console.time('mount application')
        mountBody()
        Logger.console.timeEnd('mount application')
    } else if (isBodyEvent(event)) {
        logHtmxLifecycleEvent(event, ':body')
        Logger.console.time('mount application')
        clearBody()
        window.requestAnimationFrame(() => {
            mountBody()
            Logger.console.timeEnd('mount application')
        })
    } else {
        logHtmxLifecycleEvent(event)
    }
})
doc.addEventListener("htmx:beforeHistorySave", (event) => {
    logHtmxLifecycleEvent(event)
})
doc.addEventListener("htmx:historyRestore", (event) => {
    logHtmxLifecycleEvent(event);
})