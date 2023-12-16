import { $, $$, $id, jsx } from "~/Utility/DomUtility"
import { ResizeManager } from "~/Service/ObserverCollector"
import Controller from "~/Application/Controller"

export default class ScrollbarWidth extends Controller {
    static props = {
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
        if ($id(this.sensorId)) {
            this.sensorEl = $id(this.sensorId)
        } else {
            this.sensorEl = (
                <div id={this.sensorId} data-turbo-render-excluded
                     style="width:50px; visibility:hidden; overflow:scroll; height:0px;" >
                    <div></div>
                </div>
            )
            document.body.appendChild(this.sensorEl);
        }
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