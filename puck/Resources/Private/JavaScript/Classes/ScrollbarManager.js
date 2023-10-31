import { jsx } from '../General/Aliases';
import { ResizeManager } from "./ObserverManager.js";

export default class ScrollbarManager {
    scrollbarWidth = 0
    constructor( { sensorId = 'scrollbar-width-sensor', updateOnResize = true, ...options } ) {
        Object.assign(this, { sensorId, updateOnResize, ...options })
    }
    getScrollbarWidth = () => {
        return (this.sensorElement.getBoundingClientRect().width - this.sensorElement.firstChild.getBoundingClientRect().width);
    }
    updateScrollbarWidth = () => {
        this.scrollbarWidth = this.getScrollbarWidth()
        document.documentElement.style.setProperty('--scrollbar-width', `${this.scrollbarWidth}px`)
    }
    mount() {
        document.getElementById(this.sensorId)?.remove()
        this.sensorElement = (
            <div id={this.sensorId} data-turbo-temporary
                 style="width:50px; visibility:hidden; overflow:scroll; height:0px;" >
                <div></div>
            </div>
        )
        document.body.appendChild(this.sensorElement);
        this.updateScrollbarWidth()
        if (this.updateOnResize) {
            ResizeManager.addById('scrollbar-manager', this.sensorElement.firstChild, (entry, observer) => {
                this.updateScrollbarWidth()
            })
        }
        return this
    }
    destroy() {
        this.sensorElement?.remove()
        ResizeManager.remove('scrollbar-manager')
    }
}