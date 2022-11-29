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
    } else if (typeof target === 'string' && document.getElementById(target)) {
        node = document.getElementById(target)
    } else if (typeof target === 'string' && document.querySelector(target)) {
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

export {noDragClick, getNode}


