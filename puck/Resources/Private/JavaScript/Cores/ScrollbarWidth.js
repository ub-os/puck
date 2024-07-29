import { $, $$, $id, jsx } from "~/Utility/DomUtility"
import { ResizeManager } from "~/Service/ObserverCollector"
import { Core } from "~/_jcores"

export default class ScrollbarWidth extends Core {
    static attributes = {
        sensorId: 'scrollbar-width-sensor',
        updateOnResize: true,
    }

    get scrollbarWidth() {
        return (this.sensorEl.getBoundingClientRect().width - this.sensorEl.firstChild.getBoundingClientRect().width);
    }
    updateScrollbarWidth = () => {
        document.documentElement.style.setProperty('--scrollbar-width', `${this.scrollbarWidth}px`)
    }
    connect() {
        this.sensorEl = $id(this.sensorId) || this.el.appendChild((
            <div id={this.sensorId} data-render-excluded
                 style="width:50px; visibility:hidden; overflow:scroll; height:0px;">
                <div></div>
            </div>
        ))
        this.updateScrollbarWidth()
        if (this.updateOnResize) {
            ResizeManager.addById(`scrollbar-width-${this.el.id}`, this.sensorEl.firstChild, (entry, observer) => {
                this.updateScrollbarWidth()
            })
        }
        return this
    }
    disconnect() {
        this.sensorEl.remove()
        ResizeManager.remove(`scrollbar-width-${this.el.id}`)
    }
}