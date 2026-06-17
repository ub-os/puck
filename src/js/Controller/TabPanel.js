import Showable from '#/Controller/Showable.js'

export default class TabPanel extends Showable {
	onShow(event) {
		super.onShow(event)
		this.element.style.maxHeight = `${(this.element.scrollHeight + 100).toString()}px`
		this.controlTargets.forEach(t => {
			t.ariaSelected = 'true'
		})
	}
	onHide(event) {
		super.onHide(event)
		this.element.style.maxHeight = '0'
		this.controlTargets.forEach(t => {
			t.ariaSelected = 'false'
		})
	}
	controlTargetConnected(el) {
		el.role = 'tab'
	}
	connected() {
		super.connected()
		if (!this.groupEl) {
			console.warn(`No group element found for tab panel ${this.element.id}`)
		}
		this.element.role = 'tabpanel'
		this.groupEl.role = 'tablist'
	}
	disconnected() {
		super.disconnected()
		this.element.removeAttribute('role')
		this.groupEl.removeAttribute('role')
		this.controlTargets.forEach(t => {
			t.removeAttribute('role')
		})
	}
}
