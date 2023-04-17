const noDragClick = (node, callbackFunc, delta = 6) => {
    let startX;
    let startY;
    node.addEventListener('mousedown', function (event) {
        startX = event.pageX;
        startY = event.pageY;
    });
    node.addEventListener('mouseup', function (event) {
        const diffX = Math.abs(event.pageX - startX);
        const diffY = Math.abs(event.pageY - startY);
        if (diffX < delta && diffY < delta) {
            callbackFunc(event);
        }
    });
}
const getNode = (target, objectName = '') => {
    let node = undefined
    if (target instanceof Element) {
        node = target
    } else if (typeof target === 'string' && target && document.getElementById(target)) {
        node = document.getElementById(target)
    } else if (typeof target === 'string' && target && document.querySelector(target)) {
        node = document.querySelector(target)
        if (!node.id) {
            console.error(`${objectName && objectName+': '}Provided target element does not have an id attribute.`)
            console.trace()
        }
    } else {
        console.error(`${objectName && objectName+': '}No valid element or id provided as target.`)
        console.trace()
    }
    return node
}

const scrollTo = (target, offset = 0) => {
    const node = getNode(target)
    if (node && getComputedStyle(node).position !== 'fixed') {
        const height = node.getBoundingClientRect().top + document.documentElement.scrollTop - offset;
        console.log({ node, height, offset })
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
    const node = getNode(target)
    const parents = [];
    let p = node.parentNode;
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

export {noDragClick, getNode, getParents, scrollTo, getLineBreaks}


