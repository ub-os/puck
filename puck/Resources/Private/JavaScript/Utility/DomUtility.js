const $ = (arg1, arg2 = '') => {
    if (arg2) return arg1.querySelector(arg2)
    return document.querySelector(arg1)
}
const $$ = (arg1, arg2 = '') => {
    if (arg2) return arg1.querySelectorAll(arg2)
    return document.querySelectorAll(arg1)
}
const $id = (id) => document.getElementById(id)
const $target = (target, objectName) => {
    if (target instanceof Element) return target
    if (typeof target !== 'string') {
        console.trace(`${objectName && objectName+': '}No valid element or selector provided as target.`)
        return false
    }
    let el = document.getElementById(target)
    if (el) return el
    el = document.querySelector(target)
    if (el) return el

    console.trace(`${objectName && objectName+': '}No valid element or selector provided as target.`)
    return null
}

Element.prototype.$ = function(selector) {
    return this.querySelector(selector);
};
Element.prototype.$$ = function(selector) {
    return this.querySelectorAll(selector);
};

// jsx pragma method
const jsx = (tag, props, ...children) => {
    const element = document.createElement(tag)

    Object.entries(props || {}).forEach(([name, value]) => {
        if (name.startsWith('on') && name.toLowerCase() in window)
            element.addEventListener(name.toLowerCase().substring(2), value)
        else element.setAttribute(name, value.toString())
    })

    children.forEach((child) => {
        element.appendChild(
            child.nodeName === undefined ? document.createTextNode(child.toString()) : child
        )
    })
    return element
}

const delegateListener = (selector, type, callback) => {
    document.addEventListener(type, (event) => {
        const target = event.target.closest(selector)
        if (target) {
            event.delegateTarget = target
            callback(event)
        }
    })
}

const noDragClick = (element, callbackFunc, delta = 6) => {
    let startX;
    let startY;
    const mouseDownHandler = (event) => {
        startX = event.pageX;
        startY = event.pageY;
    }
    const mouseUpHandler = (event) => {
        const diffX = Math.abs(event.pageX - startX);
        const diffY = Math.abs(event.pageY - startY);
        if (diffX < delta && diffY < delta) {
            callbackFunc(event);
        }
    }
    element.addEventListener('mousedown', mouseDownHandler);
    element.addEventListener('mouseup', mouseUpHandler);
    return {
        remove: () => {
            element.removeEventListener('mousedown', mouseDownHandler);
            element.removeEventListener('mouseup', mouseUpHandler);
        }
    }
}

const scrollToEvent = new Event('scrollTo')
const scrollTo = (target, offset = 0) => {
    const element = $target(target)
    if (element && getComputedStyle(element).position !== 'fixed') {
        const height = element.getBoundingClientRect().top + document.documentElement.scrollTop - offset;
        window.scrollTo({
            top: height,
            left: 0,
            behavior: 'smooth'
        });
    }
}

function $parents(target, parentSelector /* optional */) {
    // If no parentSelector defined will bubble up all the way to *document*
    if (parentSelector === undefined) {
        parentSelector = document;
    }
    const element = target($target)
    const parents = [];
    let p = element.parentNode;
    while (p !== parentSelector) {
        const o = p;
        parents.push(o);
        p = o.parentNode;
    }
    parents.push(parentSelector); // Push that parentSelector you wanted to stop at
    return parents;
}

const tryViewTransition = callback => {
    if (document.startViewTransition) {
        document.startViewTransition(callback)
    } else {
        callback()
    }
}

const nextFrame = callback => window.requestAnimationFrame(callback)

export {
    $,
    $$,
    $id,
    $target,
    $parents,
    jsx,
    noDragClick,
    scrollTo,
    tryViewTransition,
    nextFrame
}