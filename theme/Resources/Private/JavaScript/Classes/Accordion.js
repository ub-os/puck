
import {Toggleable, toggleEvents} from "./Toggleable"

class Accordion extends Toggleable {
    constructor(target, { ...options }) {
        super(target, { ...options })
    }
    toggleOn() {
        super.toggleOn();
        this.node.style.height = `${(this.node.scrollHeight).toString()}px`
        this.toggles.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff() {
        super.toggleOff()
        this.node.style.height = `0`
        this.toggles.forEach(t => t.ariaExpanded = 'false')
    }
    mount() {
        super.mount()
        return this
    }
}

export default Accordion
