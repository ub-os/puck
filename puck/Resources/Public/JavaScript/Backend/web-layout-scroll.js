/**
 * Web Layout Scroll Position Memory
 *
 * This script remembers the scroll position of the web layout module
 * and restores it when navigating back to the same page.
 */
{
	const scrollContainer = document.querySelector('[data-module-name="web_layout"] .module-body');
	if (scrollContainer) {
		const urlParams = new URLSearchParams(window.location.search);
		const id = urlParams.get('id');
		const lastId = localStorage.getItem('web_layout_lastPageId');
		if (lastId && id === lastId) {
		 const scrollPos = localStorage.getItem('web_layout_scrollPos');
		 if (scrollPos) {
			 window.requestAnimationFrame(() => {
				 scrollContainer.scrollTo(0, Number.parseInt(scrollPos));
			 });
		 }
		} else {
		 localStorage.setItem('web_layout_scrollPos', '0');
		}
		localStorage.setItem('web_layout_lastPageId', id);
		window.onbeforeunload = (e) => {
		 localStorage.setItem('web_layout_scrollPos', scrollContainer.scrollTop.toString());
		}
	}
}
