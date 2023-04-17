define([], function() {
    var scrollContainer = document.querySelector('[data-module-name="web_layout"]');
    window.onload = function(e) {
        var urlParams = new URLSearchParams(window.location.search);
        var id = urlParams.get('id');
        var lastId = localStorage.getItem('web_layout_lastPageId');
        if (lastId && id === lastId) {
            var scrollPos = localStorage.getItem('web_layout_scrollPos');
            if (scrollPos) scrollContainer.scrollTo(0, parseInt(scrollPos));
        } else {
            localStorage.setItem('web_layout_scrollPos', '0');
        }
        localStorage.setItem('web_layout_lastPageId', id);
    };
    window.onbeforeunload = function(e) {
        localStorage.setItem('web_layout_scrollPos', scrollContainer.scrollTop.toString());
    };
});

