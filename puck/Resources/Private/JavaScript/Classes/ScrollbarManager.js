import { ResizeManager } from "./ObserverManager";

export default class ScrollbarManager {
    scrollbarWidth = 0
    constructor( { updateOnResize = true, ...options } ) {
        this.updateOnResize = updateOnResize
        // random id
        this.identifier = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
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
            ResizeManager.addById('scrollbar-manager', document.documentElement, (entry, observer) => {
                window.requestAnimationFrame(() => {
                    this.updateScrollbarWidth()
                })
            })
        }
        return this
    }
    destroy() {
        ResizeManager.remove('scrollbar-manager')
    }
}