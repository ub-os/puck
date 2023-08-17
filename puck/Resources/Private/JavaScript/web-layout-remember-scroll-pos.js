define([], function() {
    const scrollContainer = document.querySelector('[data-module-name="web_layout"]');
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const lastId = localStorage.getItem('web_layout_lastPageId');
    if (lastId && id === lastId) {
        const scrollPos = localStorage.getItem('web_layout_scrollPos');
        if (scrollPos) scrollContainer.scrollTo(0, parseInt(scrollPos));
    } else {
        localStorage.setItem('web_layout_scrollPos', '0');
    }
    localStorage.setItem('web_layout_lastPageId', id);
    window.onbeforeunload = function(e) {
        localStorage.setItem('web_layout_scrollPos', scrollContainer.scrollTop.toString());
    };
});
