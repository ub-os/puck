const $ = (selector) => document.querySelector(selector)
const $$ = (selector) => document.querySelectorAll(selector)
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
    return false
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

function getLineBreaks(node) {
    // we only deal with TextNodes
    if (!node || !node.parentNode || node.nodeType !== 3)
        return [];
    // our Range object form which we'll get the characters positions
    const range = document.createRange();
    // here we'll store all our lines
    const lines = [];
    // begin at the first char
    range.setStart(node, 0);
    // initial position
    let prevBottom = range.getBoundingClientRect().bottom;
    let str = node.textContent;
    let current = 1; // we already got index 0
    let lastFound = 0;
    let bottom = 0;
    // iterate over all characters
    while (current <= str.length) {
        // move our cursor
        range.setStart(node, current);
        if (current < str.length - 1)
            range.setEnd(node, current + 1);
        bottom = range.getBoundingClientRect().bottom;
        if (bottom > prevBottom) { // line break
            lines.push(
                str.substring(lastFound, current - lastFound) // text content
            );
            prevBottom = bottom;
            lastFound = current;
        }
        current++;
    }
    lines.push(str.substr(lastFound));
    return lines;
}

export {
    $,
    $$,
    $id,
    $target,
    $parents,
    jsx,
    noDragClick,
    scrollTo,
    getLineBreaks
}