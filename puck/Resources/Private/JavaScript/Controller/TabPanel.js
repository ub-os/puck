import Toggleable from "~/Controller/Toggleable"

export default class TabPanel extends Toggleable {
    static displayName = 'TabPanel'
    toggleOn(transition= true) {
        super.toggleOn(transition)
        this.el.style.maxHeight = `${(this.el.scrollHeight + 100).toString()}px`
        this.toggles.forEach(t => t.ariaSelected = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.el.style.maxHeight = `0`
        this.toggles.forEach(t => t.ariaSelected = 'false')
    }
    connect() {
        super.connect()
        if (!this.groupEl) {
            console.warn(`No group element found for tab panel ${this.el.id}`)
        }
        this.el.role = 'tabpanel'
        this.groupEl.role = 'tablist'
        this.toggles.forEach(t => { t.role = 'tab' })
        return this
    }
    disconnect() {
        super.disconnect()
        this.el.removeAttribute('role')
        this.groupEl.removeAttribute('role')
        this.toggles.forEach(t => t.removeAttribute('role'))
    }
}
