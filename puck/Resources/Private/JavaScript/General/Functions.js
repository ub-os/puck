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
export {noDragClick, getNode, getParents, scrollTo}


