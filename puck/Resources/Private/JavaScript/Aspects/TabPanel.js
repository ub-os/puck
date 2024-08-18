import Showable from "~/Aspects/Showable"

export default class TabPanel extends Showable {
    static displayName = 'TabPanel'
    show({ transition = true }) {
        super.show({ transition })
        this.el.style.maxHeight = `${(this.el.scrollHeight + 100).toString()}px`
        this.toggleElements.forEach(t => t.ariaSelected = 'true')
    }
    hide({ transition = true, changeUrlHash = true }) {
        super.hide({ transition, changeUrlHash })
        this.el.style.maxHeight = `0`
        this.toggleElements.forEach(t => t.ariaSelected = 'false')
    }
    toggleElementConnected(el) {
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
        this.toggleElements.forEach(t => t.removeAttribute('role'))
    }
}
