import htmx from 'htmx.org'
import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, jsx, scrollTo } from '~/Utility/DomUtility'
import Logger from '~/Service/Logger'
import App from '~/Application/Application'
import '~/register'

htmx.config.scrollBehavior = 'auto'
htmx.config.defaultSwapStyle = 'outerHTML'
htmx.config.globalViewTransitions = true

let isInitialLoad = true

smoothscroll.polyfill()

const doc = document.documentElement

const mountBody = () => {
    window.requestAnimationFrame(() => {
        document.body.classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => element.classList.remove('u-initially-hidden'))
    })
    Logger.console.log(App)
}

const clearBody = () => {
    //$$('[data-render-excluded]').forEach(el => el.remove())
    $$('.--scroll').forEach(el => el.classList.remove('--scroll'))
    App.disconnect()
}

const isBodyEvent = event => {
    return event.detail.boosted || event.detail.elt.tagName === 'BODY' || event.detail.elt.hasAttribute('data-hx-boost-swap-target')
}

const logEvent = (event, eventTypeSuffix = '') => {
    Logger.console.log(`%c${event.type}${eventTypeSuffix}`, "color:lightgreen", event)
}

doc.addEventListener("htmx:beforeRequest", (event) => {
    if (isBodyEvent(event)) {
        logEvent(event, ':body')
    } else {
        logEvent(event)
    }
    const targetAnchor = event.detail.elt.closest('a')
    if (targetAnchor?.hash && targetAnchor.pathname === window.location.pathname) {
        event.preventDefault()
    }
    //frameExitAnimation(event.target)
})
doc.addEventListener("htmx:beforeSwap", (event) => {
    if (isBodyEvent(event)) {
        logEvent(event, ':body')
    } else {
        logEvent(event)
    }
    if (event.target.hasAttribute('data-hx-target-scroll')) {
        scrollTo(event.target)
    }
    //frameEnterAnimation(event.target)
})
doc.addEventListener("htmx:afterSwap", (event) => {
    if (isBodyEvent(event)) {
        logEvent(event, ':body')
        clearBody()
    } else {
        logEvent(event)
    }
})
doc.addEventListener("htmx:load", (event) => {
    if (isInitialLoad) {
        isInitialLoad = false
        logEvent(event, ':initial')
        console.time('load')
        App.connect()
        mountBody()
        console.timeEnd('load')
    } else if (isBodyEvent(event)) {
        logEvent(event, ':body')
        console.time('load')
        mountBody()
        console.timeEnd('load')
    } else {
        logEvent(event)
    }
})
doc.addEventListener("htmx:beforeHistorySave", (event) => {
    logEvent(event)
})
doc.addEventListener("htmx:historyRestore", (event) => {
    logEvent(event);
    $$('[data-render-excluded]').forEach(el => el.remove())
})

/*  console.log('test action removal')
    $$('[data-action]').forEach(el => {
        const parent = el.parentNode
        const before = el.nextSibling
        // remove
        el.remove()
        // add back in
        parent.insertBefore(el, before)
    })*/






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

