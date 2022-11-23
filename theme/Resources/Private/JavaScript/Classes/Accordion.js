
import {Toggleable, toggleEvents} from "./Toggleable"

class Accordion extends Toggleable {
    constructor(target, { ...options }) {
        super(target, { ...options })
    }
    toggleOn() {
        super.toggleOn();
        this.node.style.maxHeight = `${(this.node.scrollHeight + 100).toString()}px`
        this.toggles.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff() {
        super.toggleOff()
        this.node.style.maxHeight = `0`
        this.toggles.forEach(t => t.ariaExpanded = 'false')
    }
}

export default Accordion
