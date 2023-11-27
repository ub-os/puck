import { $, $$, $id, jsx } from "~/Utility/DomUtility";
import { ResizeManager } from "./ObserverCollector";

export default class ScrollbarWidth {
    constructor({ sensorId = 'scrollbar-width-sensor', updateOnResize = true, ...options }) {
        Object.assign(this, { sensorId, updateOnResize, ...options })
        return this
    }
    get scrollbarWidth() {
        return (this.sensorEl.getBoundingClientRect().width - this.sensorEl.firstChild.getBoundingClientRect().width);
    }
    updateScrollbarWidth = () => {
        document.documentElement.style.setProperty('--scrollbar-width', `${this.scrollbarWidth}px`)
    }
    start() {
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
            ResizeManager.addById('scrollbar-manager', this.sensorEl.firstChild, (entry, observer) => {
                this.updateScrollbarWidth()
            })
        }
        return this
    }
}