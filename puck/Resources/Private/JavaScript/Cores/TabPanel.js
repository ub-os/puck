import Toggleable from "~/Cores/Toggleable"

export default class TabPanel extends Toggleable {
    static displayName = 'TabPanel'
    toggleOn(transition= true) {
        super.toggleOn(transition)
        this.el.style.maxHeight = `${(this.el.scrollHeight + 100).toString()}px`
        this.toggleUnits.forEach(t => t.ariaSelected = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.el.style.maxHeight = `0`
        this.toggleUnits.forEach(t => t.ariaSelected = 'false')
    }
    toggleUnitConnected(el) {
        el.role = 'tab'
    }
    connect() {
        super.connect()
        if (!this.groupEl) {
            console.warn(`No group element found for tab panel ${this.el.id}`)
        }
        this.el.role = 'tabpanel'
        this.groupEl.role = 'tablist'
        return this
    }
    disconnect() {
        super.disconnect()
        this.el.removeAttribute('role')
        this.groupEl.removeAttribute('role')
        this.toggleUnits.forEach(t => t.removeAttribute('role'))
    }
}
