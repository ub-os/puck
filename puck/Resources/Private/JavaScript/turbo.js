import * as Turbo from "@hotwired/turbo"
import { $, $$, jsx } from '~/General/Aliases'
import App from '~/Classes/Application'
import startBody from '~/behaviors'



const docEl = document.documentElement
let visitIsFrameAction = false
let visitIsRestoration = false
let renderIsPostPreviewRender = false

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

docEl.addEventListener("turbo:click", (event) => {
    console.log(event.type, event)
    if (event.target.hash && event.target.pathname === window.location.pathname) {
        event.preventDefault()
    }
    if (event.target.hasAttribute('data-turbo-action') && event.target.getAttribute('data-turbo-frame') !== '_top') {
        visitIsFrameAction = true
        setTimeout(() => {
            visitIsFrameAction = false
        }, 2000)
    }
})
docEl.addEventListener("turbo:before-fetch-request", (event) => {
    console.log('before-fetch-request', event)
    //frameExitAnimation(event.target)
})
docEl.addEventListener("turbo:before-frame-render", (event) => {
    console.log(event.type, event)
    //frameEnterAnimation(event.target)
    frameViewTransition(event)
})
docEl.addEventListener("turbo:frame-render", (event) => {
    console.log(event.type, event)
})
docEl.addEventListener("turbo:frame-load", (event) => {
    console.log(event.type, event)
})
docEl.addEventListener("turbo:visit", (event) => {
    console.log(event.type, event)
    if (event.detail.action == 'restore') {
        visitIsRestoration = true
    }
})

let visitFrameActionCacheExcluded = []
docEl.addEventListener("turbo:before-cache", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })

    if (visitIsFrameAction) {
    }
})
docEl.addEventListener("turbo:before-render", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
    if (!visitIsFrameAction) {
        App.observerManager.destroy()
        event.preventDefault()
        event.detail.newBody.$$('[data-turbo-render-excluded]').forEach(el => el.remove())
        event.detail.newBody.$$('.--scroll').forEach(el => el.classList.remove('--scroll'))
        event.detail.resume()
    }
    if (!visitIsFrameAction && !visitIsRestoration) {
        //frameEnterAnimation(event.target)
        frameViewTransition(event)
    }
})
docEl.addEventListener("turbo:render", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
})
docEl.addEventListener("turbo:load", (event) => {
    console.log(event.type, { event, visitIsFrameAction, visitIsRestoration, renderIsPostPreviewRender })
    window.dispatchEvent(new Event('scroll'))

    if (visitIsFrameAction) {
        visitIsFrameAction = false
    } else {
        startBody()
    }

    if (visitIsRestoration) visitIsRestoration = false
    renderIsPostPreviewRender = docEl.hasAttribute('data-turbo-preview')
})


