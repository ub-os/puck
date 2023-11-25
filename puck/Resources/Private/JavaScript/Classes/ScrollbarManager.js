import { $, $$, id$, jsx } from "~/General/Aliases";
import { ResizeManager } from "./ObserverManager";

export default class ScrollbarManager {
    scrollbarWidth = 0
    constructor({ sensorId = 'scrollbar-width-sensor', updateOnResize = true, ...options }) {
        Object.assign(this, { sensorId, updateOnResize, ...options })
    }
    getScrollbarWidth = () => {
        return (this.sensorEl.getBoundingClientRect().width - this.sensorEl.firstChild.getBoundingClientRect().width);
    }
    updateScrollbarWidth = () => {
        this.scrollbarWidth = this.getScrollbarWidth()
        document.documentElement.style.setProperty('--scrollbar-width', `${this.scrollbarWidth}px`)
    }
    mount() {
        this.sensorEl = (
            <div id={this.sensorId} data-turbo-render-excluded
                 style="width:50px; visibility:hidden; overflow:scroll; height:0px;" >
                <div></div>
            </div>
        )
        document.body.appendChild(this.sensorEl);
        this.updateScrollbarWidth()
        if (this.updateOnResize) {
            ResizeManager.addById('scrollbar-manager', this.sensorEl.firstChild, (entry, observer) => {
                this.updateScrollbarWidth()
            })
        }
        return this
    }
    destroy() {
        this.sensorEl?.remove()
        ResizeManager.remove('scrollbar-manager')
    }
}