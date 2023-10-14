export default class ScrollbarManager {
    scrollbarWidth = 0
    constructor( { updateOnResize = true, ...options} ) {
        this.updateOnResize = updateOnResize
    }
    getScrollbarWidth = () => {
        let box = document.createElement('div');
        box.style.overflow = 'scroll';
        document.body.appendChild(box);
        const width = box.offsetWidth - box.clientWidth;
        document.body.removeChild(box);
        return width;
    }
    updateScrollbarWidth = () => {
        this.scrollbarWidth = this.getScrollbarWidth()
        document.documentElement.style.setProperty('--scrollbar-width', `${this.scrollbarWidth}px`)
    }
    mount() {
        this.updateScrollbarWidth()
        if (this.updateOnResize) {
            this.resizeObserver = new ResizeObserver(entries => {
                for (let entry of entries) {
                    window.requestAnimationFrame(() => {
                        this.updateScrollbarWidth()
                    })
                }
            })
            this.resizeObserver.observe(document.documentElement)
        }
        return this
    }
    destroy() {
        this.resizeObserver.unobserve(document.documentElement)
        delete this.resizeObserver
    }
}