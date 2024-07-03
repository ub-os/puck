import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, $id, jsx, scrollTo } from '~/_jcores/Utility/DomUtility'
import { ObserverCollector } from '~/Service/ObserverCollector'
import Logger from '~/Service/Logger'
import htmx from '~/htmx'
import 'htmx-ext-head-support'
import 'htmx-ext-preload'
import Nexus from '~/nexus'


htmx.config.scrollBehavior = 'auto'
htmx.config.defaultSwapStyle = 'outerHTML'
htmx.config.defaultSwapDelay = 0
htmx.config.defaultSettleDelay = 0
htmx.config.globalViewTransitions = true
htmx.config.allowScriptTags = true
htmx.config.allowEval = false


smoothscroll.polyfill()
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
    Nexus.connect()
    window.requestAnimationFrame(() => {
        $id('root').classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => element.classList.remove('u-initially-hidden'))
        //document.body.dispatchEvent(new CustomEvent('toggle-off-all', {detail: {transition: false}}))
    })
    Logger.console.log(`%capplication:mount`, "color:orange")
    Logger.console.log(Nexus)
    isMounted = true
}

const clearBody = () => {
    if (!isMounted) return
    Logger.console.time('body cleanup')
    $$('[data-render-excluded]').forEach(el => el.remove())
    $$('.--scroll').forEach(el => el.classList.remove('--scroll'))
    Nexus.disconnect()
    ObserverCollector.instance.clear()
    Logger.console.log(`%capplication:clear`, "color:orange")
    Logger.console.timeEnd('body cleanup')
    isMounted = false
}

const resetBody = () => {
    Logger.console.time('mount application')
    clearBody()
    window.requestAnimationFrame(() => {
        mountBody()
        Logger.console.timeEnd('mount application')
        window.UC_UI?.restartCMP() // restart Usercentrics CMP UI if available
    })
}

const isBodyEvent = event => {
    return event.detail.target?.tagName === 'BODY' || event.detail.target?.id === 'root' || event.detail.elt?.id === 'root'
}
const logHtmxLifecycleEvent = (event, eventTypeSuffix = '') => {
    Logger.console.log(`%c${event.type}${eventTypeSuffix}`, "color:lightgreen", event)
}

let currentlySubmittingForm = null
doc.addEventListener("htmx:beforeRequest", (event) => {
    // disable multiple form requests at the same time
    if (event.target.tagName == 'FORM') {
        if (currentlySubmittingForm || event.target.classList.contains('htmx-request')) {
            //event.preventDefault()
            //return
        } else {
            currentlySubmittingForm = event.target
        }
    }
    if (isBodyEvent(event)) {
        logHtmxLifecycleEvent(event, ':body')
    } else {
        logHtmxLifecycleEvent(event)
    }
})

doc.addEventListener("htmx:afterRequest", (event) => {
    // disable multiple form requests at the same time -> end cycle
    if (event.target.tagName == 'FORM') {
        window.requestAnimationFrame(() => currentlySubmittingForm = null)
    }
    if (isBodyEvent(event)) {
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
        event.target.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
})
doc.addEventListener("htmx:oobBeforeSwap", (event) => {
    logHtmxLifecycleEvent(event)
})
doc.addEventListener("htmx:afterSwap", (event) => {
    if (isBodyEvent(event)) {
        logHtmxLifecycleEvent(event, ':body')
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
        resetBody()
    } else {
        logHtmxLifecycleEvent(event)
    }
})
doc.addEventListener("htmx:beforeHistorySave", (event) => {
    logHtmxLifecycleEvent(event)
})
doc.addEventListener("htmx:historyRestore", (event) => {
    logHtmxLifecycleEvent(event)
    resetBody()
})

doc.addEventListener("htmx:responseError", (event) => {
    // route to error page
    window.location.href = event.detail.xhr.responseURL;
})