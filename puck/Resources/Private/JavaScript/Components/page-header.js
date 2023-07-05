import { $, $$, jsx } from '../General/Aliases';

function hideHeaderOnScrollDown() {
    const minScrollForHide = 300
    const pageHeaderElement = $('[data-page-header]')
    const stateClasses = {
        hidden: '--scroll-down',
        visible: '--scroll-up'
    }
    let lastScrollTop = 0
    let ticking = false
    let lastScrollDirection = 'down'
    let lastState = 'visible'

    function onScroll() {
        if (!ticking) {
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
        // Perform your desired actions based on the scroll direction here
    }

    window.addEventListener('scroll', onScroll)
}

// Call the function to start detecting scroll direction
hideHeaderOnScrollDown()