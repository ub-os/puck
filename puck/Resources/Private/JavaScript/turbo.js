import * as Turbo from "@hotwired/turbo"
import smoothscroll from 'smoothscroll-polyfill'
import { $, $$, jsx } from '~/Utility/DomUtility'
import Logger from '~/Service/Logger'
import App from '~/Application/Application'
import '~/register'

smoothscroll.polyfill();

const doc = document.documentElement
let visitIsFrameAction = false
let visitIsRestoration = false
let renderIsPostPreviewRender = false

const newBodyMount = () => {
    App.connect()
    window.requestAnimationFrame(() => {
        document.body.classList.remove('u-no-transition')
        $$('.u-initially-hidden').forEach(element => element.classList.remove('u-initially-hidden'))
    })
    Logger.console.log(App)
}

const newBodyCleanup = (newBody) => {
    App.reset()
    newBody.$$('[data-turbo-render-excluded]').forEach(el => el.remove())
    newBody.$$('.--scroll').forEach(el => el.classList.remove('--scroll'))
}

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

doc.addEventListener("turbo:click", (event) => {
    Logger.console.log(event.type, event)
    if (event.target.hash && event.target.pathname === window.location.pathname) {
        event.preventDefault()
    }
    visitIsFrameAction = false
    if (event.target.hasAttribute('data-turbo-action') && event.target.getAttribute('data-turbo-frame') !== '_top') {
        visitIsFrameAction = true
    }
})
doc.addEventListener("turbo:before-fetch-request", (event) => {
    Logger.console.log('before-fetch-request', event)
    //frameExitAnimation(event.target)
})
doc.addEventListener("turbo:before-frame-render", (event) => {
    Logger.console.log(event.type, event)
    //frameEnterAnimation(event.target)
    frameViewTransition(event)
})
doc.addEventListener("turbo:frame-render", (event) => {
    Logger.console.log(event.type, event)
})
doc.addEventListener("turbo:frame-load", (event) => {
    Logger.console.log(event.type, event)
})
doc.addEventListener("turbo:visit", (event) => {
    Logger.console.log(event.type, event)
    if (event.detail.action == 'restore') {
        visitIsRestoration = true
    }
})

doc.addEventListener("turbo:before-cache", (event) => {
    Logger.console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
    if (visitIsFrameAction) {
    }
})
doc.addEventListener("turbo:before-render", (event) => {
    Logger.console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
    if (!visitIsFrameAction) {
        event.preventDefault()
        newBodyCleanup(event.detail.newBody)
        frameViewTransition(event)
    }
    if (!visitIsFrameAction && !visitIsRestoration) {
    }
})
doc.addEventListener("turbo:render", (event) => {
    Logger.console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
})
doc.addEventListener("turbo:load", (event) => {
    console.time('load')
    Logger.console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
    window.dispatchEvent(new Event('scroll'))

    if (visitIsFrameAction) {
        visitIsFrameAction = false
    } else if (!doc.hasAttribute('data-turbo-preview')) {
        newBodyMount()
        console.log('test action removal')
        $$('[data-action]').forEach(el => {
            const parent = el.parentNode
            const before = el.nextSibling
            // remove
            el.remove()
            // add back in
            parent.insertBefore(el, before)
        })
    }
    console.timeEnd('load')

    if (visitIsRestoration) visitIsRestoration = false
    renderIsPostPreviewRender = doc.hasAttribute('data-turbo-preview')
})


