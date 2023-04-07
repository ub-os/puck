
import {Toggleable, toggleEvents} from "./Toggleable"

class Accordion extends Toggleable {
    constructor(target, { useMinHeight = false, ...options }) {
        super(target, { useMinHeight, ...options })
        Object.assign(this, { useMinHeight })
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        if (this.useMinHeight) {
            this.node.style.minHeight = `${(this.node.scrollHeight).toString()}px`
        } else {
            this.node.style.height = `${(this.node.scrollHeight).toString()}px`
        }
        this.toggles.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff(transition= true) {
        super.toggleOff(transition)
        if (this.useMinHeight) {
            this.node.style.minHeight = `0`
        } else {
            this.node.style.height = `0`
        }
        this.toggles.forEach(t => t.ariaExpanded = 'false')
    }
    mount() {
        super.mount()
        return this
    }
}

export default Accordion
