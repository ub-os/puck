import Toggleable from "./Toggleable"

class TabPanel extends Toggleable {
    constructor(target, { ...options }) {
        super(target, { ...options })
        if (!this.groupElement) {
            console.error(`TabPanel: No group provided for ${this.id}`)
            console.trace()
        }
    }
    toggleOn(transition= true) {
        super.toggleOn(transition)
        this.element.style.maxHeight = `${(this.element.scrollHeight + 100).toString()}px`
        this.toggles.forEach(t => t.ariaSelected = 'true')
    }
    toggleOff(transition= true) {
        super.toggleOff(transition)
        this.element.style.maxHeight = `0`
        this.toggles.forEach(t => t.ariaSelected = 'false')
    }
    mount() {
        super.mount()
        this.element.role = 'tabpanel'
        this.groupElement.role = 'tablist'
        this.toggles.forEach(t => { t.role = 'tab' })
        return this
    }
}

export default TabPanel
