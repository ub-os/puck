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
export {noDragClick};

