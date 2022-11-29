
import {Toggleable, toggleEvents} from "./Toggleable"

class TabPanel extends Toggleable {
    constructor(target, { ...options }) {
        super(target, { ...options })
        if (!this.groupNode) {
            console.error(`TabPanel: No group provided for ${this.id}`)
            console.trace()
        }
    }
    toggleOn() {
        super.toggleOn();
        this.node.style.maxHeight = `${(this.node.scrollHeight + 100).toString()}px`
        this.toggles.forEach(t => t.ariaSelected = 'true')
    }
    toggleOff() {
        super.toggleOff()
        this.node.style.maxHeight = `0`
        this.toggles.forEach(t => t.ariaSelected = 'false')
    }
    mount() {
        super.mount()
        this.node.role = 'tabpanel'
        this.groupNode.role = 'tablist'
        this.toggles.forEach(t => { t.role = 'tab' })
        return this
    }
}

export default TabPanel
