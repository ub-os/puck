const noDragClick = (element, callbackFunc, delta = 6) => {
    let startX;
    let startY;
    element.addEventListener('mousedown', function (event) {
        startX = event.pageX;
        startY = event.pageY;
    });
    element.addEventListener('mouseup', function (event) {
        const diffX = Math.abs(event.pageX - startX);
        const diffY = Math.abs(event.pageY - startY);
        if (diffX < delta && diffY < delta) {
            callbackFunc(event);
        }
    });
}
const getElement = (target, objectName = '') => {
    let element = undefined
    if (target instanceof Element) {
        element = target
    } else if (typeof target === 'string' && target && document.getElementById(target)) {
        element = document.getElementById(target)
    } else if (typeof target === 'string' && target && document.querySelector(target)) {
        element = document.querySelector(target)
        if (!element.id) {
            console.error(`${objectName && objectName+': '}Provided target element does not have an id attribute.`)
            console.trace()
        }
    } else {
        console.error(`${objectName && objectName+': '}No valid element or id provided as target.`)
        console.trace()
    }
    return element
}

const scrollToEvent = new Event('scrollTo')
const scrollTo = (target, offset = 0) => {
    const element = getElement(target)
    document.body.dispatchEvent(scrollToEvent)
    if (element && getComputedStyle(element).position !== 'fixed') {
        const height = element.getBoundingClientRect().top + document.documentElement.scrollTop - offset;
        window.scrollTo({
            top: height,
            left: 0,
            behavior: 'smooth'
        });
    }
}

function getParents(target, parentSelector /* optional */) {
    // If no parentSelector defined will bubble up all the way to *document*
    if (parentSelector === undefined) {
        parentSelector = document;
    }
    const element = getElement(target)
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
                str.substr(lastFound, current - lastFound) // text content
            );
            prevBottom = bottom;
            lastFound = current;
        }
        current++;
    }
    lines.push(str.substr(lastFound));
    return lines;
}

function kebabCase(string) {
    const upper = /(?<!\p{Uppercase_Letter})\p{Uppercase_Letter}|\p{Uppercase_Letter}(?!\p{Uppercase_Letter})/gu;
    return string.replace(upper, "-$&").replace(/^-/, "").toLowerCase();
}

function jsonParseValue(val) {
    let obj = {}
    try {
        obj = JSON.parse(val || '{}')
    } catch (e) {
        return {}
    }
    return obj
}

export { noDragClick, getElement, getParents, scrollTo, getLineBreaks, kebabCase, jsonParseValue }
