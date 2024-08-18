import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, $id, jsx, scrollTo } from '~/Utility/DomUtility'
import Logger from '~/Service/Logger'
import { app, htmx } from '~/setup'
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
app.connect()
Logger.console.log(app)

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

on("htmx:beforeRequest", (event) => {
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
    }
})

const isBodySwapEvent = event => {
    return event.target?.tagName === 'BODY' || event.target?.id === 'root' || event.elt?.id === 'root'
}

/*
window.puckApp = {
    nexus: Nexus,
    isInitialLoad: true,
    isMounted: false
}
// fix for htmx initializing on DOMContentLoaded or if document.readyState is 'complete'
// because in this setup DOMContentLoaded is usually fired BEFORE readyState is 'complete'
// let domContentLoaded = document.readyState === 'complete'
// document.addEventListener('DOMContentLoaded', () => domContentLoaded = true)
// document.addEventListener('readystatechange', e => {
//     if (domContentLoaded || document.readyState !== 'complete') return
//     document.dispatchEvent(new Event('DOMContentLoaded'))
// })

const mountBody = () => {
    if (window.puckApp.isMounted) return
    Nexus.connect()
    window.requestAnimationFrame(() => {
        $id('body').classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => element.classList.remove('u-initially-hidden'))
        //document.body.dispatchEvent(new CustomEvent('toggle-off-all', {detail: {transition: false}}))
    })
    Logger.console.log(`%capplication:mount`, "color:orange")
    Logger.console.log(Nexus)
    window.puckApp.isMounted = true
}

const clearBody = () => {
    if (!window.puckApp.isMounted) return
    Logger.console.time('body cleanup')
    $$('[data-render-excluded]').forEach(el => el.remove())
    $$('.--scroll').forEach(el => el.classList.remove('--scroll'))
    Nexus.disconnect()
    ObserverCollector.instance.clear()
    Logger.console.log(`%capplication:clear`, "color:orange")
    Logger.console.timeEnd('body cleanup')
    window.puckApp.isMounted = false
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
    if (window.puckApp.isInitialLoad) {
        window.puckApp.isInitialLoad = false
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
})*/
