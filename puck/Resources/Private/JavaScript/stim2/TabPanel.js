import Showable from '~/stim2/Showable'

export default class TabPanel extends Showable {
	onShow(event) {
		super.onShow(event)
		this.el.style.maxHeight = `${(this.el.scrollHeight + 100).toString()}px`
		this.controlRefs.forEach(t => (t.ariaSelected = 'true'))
	}
	onHide(event) {
		super.onHide(event)
		this.el.style.maxHeight = '0'
		this.controlRefs.forEach(t => (t.ariaSelected = 'false'))
	}
	controlRefConnected(el) {
		el.role = 'tab'
	}
	connected() {
		super.connected()
		if (!this.groupEl) {
			console.warn(`No group element found for tab panel ${this.el.id}`)
		}
		this.el.role = 'tabpanel'
		this.groupEl.role = 'tablist'
		return this
	}
	disconnected() {
		super.disconnected()
		this.el.removeAttribute('role')
		this.groupEl.removeAttribute('role')
		this.controlRefs.forEach(t => t.removeAttribute('role'))
	}
}
