import { ResizeManager } from "./ObserverManager.js";

export default class ScrollbarManager {
    scrollbarWidth = 0
    constructor( { sensorId = 'scrollbar-width-sensor', updateOnResize = true, ...options } ) {
        Object.assign(this, { sensorId, updateOnResize, ...options })
    }
    getScrollbarWidth = () => {
        return (this.outer.getBoundingClientRect().width - this.inner.getBoundingClientRect().width);
    }
    updateScrollbarWidth = () => {
        this.scrollbarWidth = this.getScrollbarWidth()
        document.documentElement.style.setProperty('--scrollbar-width', `${this.scrollbarWidth}px`)
    }
    mount() {
        document.getElementById(this.sensorId)?.remove()
        this.outer = document.createElement('div');
        this.outer.id = this.sensorId
        this.outer.setAttribute('style', 'width:50px; visibility:hidden; overflow:scroll; height:0px;')
        document.body.appendChild(this.outer);
        this.inner = document.createElement('div');
        this.outer.appendChild(this.inner);
        this.updateScrollbarWidth()
        if (this.updateOnResize) {
            ResizeManager.addById('scrollbar-manager', this.inner, (entry, observer) => {
                this.updateScrollbarWidth()
            })
        }
        return this
    }
    destroy() {
        this.outer.remove()
        ResizeManager.remove('scrollbar-manager')
    }
}