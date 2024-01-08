import htmx from 'htmx.org'
import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, $id, jsx, scrollTo } from '~/Utility/DomUtility'
import Logger from '~/Service/Logger'
import App from '~/Application/Application'
import '~/register'

htmx.config.scrollBehavior = 'auto'
htmx.config.defaultSwapStyle = 'outerHTML'
htmx.config.globalViewTransitions = true

let isInitialLoad = true
let isMounted = false

smoothscroll.polyfill()

const doc = document.documentElement

const mountBody = () => {
    if (isMounted) return
    Logger.console.log(`%capplication:mount`, "color:orange")
    App.connect()
    window.requestAnimationFrame(() => {
        $id('root').classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => element.classList.remove('u-initially-hidden'))
        document.body.dispatchEvent(new CustomEvent('toggle-off-all', {detail: {transition: false}}))
    })
    Logger.console.log(App)
    isMounted = true
}

const clearBody = () => {
    if (!isMounted) return
    Logger.console.log(`%capplication:clear`, "color:orange")
    $$('[data-render-excluded]').forEach(el => el.remove())
    $$('.--scroll').forEach(el => el.classList.remove('--scroll'))
    App.disconnect()
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
        logHtmxLifecycleEvent(event, ':body')
        if ((new URL(event.detail.pathInfo.requestPath)).pathname === window.location.pathname) {
            event.preventDefault()
        }
    } else {
        logHtmxLifecycleEvent(event)
    }
    //frameExitAnimation(event.target)
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
    //frameEnterAnimation(event.target)
})
doc.addEventListener("htmx:afterSwap", (event) => {
    if (isBodyEvent(event)) {
        logHtmxLifecycleEvent(event, ':body')
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
    //$$('[data-render-excluded]').forEach(el => el.remove())
})

const frameExitAnimation = (
    event,
    keyframes = [{ opacity: 1 }, { opacity: 0 }],
    timing = { duration: 200, easing: 'ease-in-out', fill: 'forwards' }
) => {
    event.target.turboFrameAnimation = event.target.animate(keyframes, timing)
}

const frameEnterAnimation = (
    event,
    keyframes = [{ opacity: 0 }, { opacity: 1 }],
    timing = { duration: 200, easing: 'ease-in-out', fill: 'forwards' }
) => {
    event.preventDefault();
    if (event.target.turboFrameAnimation) {
        event.target.turboFrameAnimation.finished.then(() => {
            event.detail.resume()
            event.target.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 200, easing: 'ease-in-out', fill: 'forwards' })
            event.target.turboFrameAnimation = null
        })
    } else {
        event.detail.resume()
        event.target.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 200, easing: 'ease-in-out', fill: 'forwards' })
    }
}
const frameViewTransition = (event) => {
    if (document.startViewTransition) {
        event.preventDefault();
        document.startViewTransition(() => {
            event.detail.resume();
        });
    }
}