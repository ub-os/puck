import { $, $$, jsx } from '../General/Aliases';
import ScrollSensitive from "../Classes/ScrollSensitive.js";

// hide page header at scroll down and show at scroll up
function headerScrollSensitivity() {
    const pageHeaderElement = $('[data-page-header]')
    let scrollTop = 1
    if (window.innerWidth > 800) {
        scrollTop = 30
    }
    const pageHeaderScrollSensitive = new ScrollSensitive(pageHeaderElement, { scrollTop }).mount()
    const minScrollForHide = 300
    const stateClasses = {
        hidden: '--scroll-down',
        visible: '--scroll-up'
    }
    let lastScrollTop = 0
    let ticking = false
    let paused = false
    let lastScrollDirection = 'down'
    let lastState = 'visible'
    function onScroll() {
        if (!ticking && !paused) {
            window.requestAnimationFrame(function () {
                const scrollDirection = checkScrollDirection()
                const newState = scrollDirection === 'down' && lastScrollTop > minScrollForHide ? 'hidden' : 'visible'
                lastScrollTop = window.pageYOffset || document.documentElement.scrollTop
                ticking = false
                lastScrollDirection = scrollDirection
                if (lastState === newState) return
                lastState = newState
                if (scrollDirection === 'down' && lastScrollTop > minScrollForHide) {
                    pageHeaderElement.classList.add(stateClasses.hidden)
                    pageHeaderElement.classList.remove(stateClasses.visible)
                } else {
                    pageHeaderElement.classList.add(stateClasses.visible)
                    pageHeaderElement.classList.remove(stateClasses.hidden)
                }
            })
            ticking = true;
        }
    }
    function checkScrollDirection() {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop
        const scrollDirection = currentScrollTop > lastScrollTop ? 'down' : 'up'
        return scrollDirection
    }

    // pause showing of header when scroll up is triggered by the scrollTo function, e.g. a page anchor link
    document.body.addEventListener('scrollTo', e => {
        paused = true
        pageHeaderElement.classList.add(stateClasses.hidden)
        pageHeaderElement.classList.remove(stateClasses.visible)
        setTimeout(() => {
            paused = false
        }, 1000)
    })
    window.addEventListener('scroll', onScroll)
}

headerScrollSensitivity()