import Showable from '~/stim2/Showable'

export default class Accordion extends Showable {
	static props = {
		...Showable.props,
		useMinHeight: false,
		pauseMediaOnHide: true,
	}
	controlRefConnected(el) {
		super.controlRefConnected(el)
		if (this.active) {
			el.ariaExpanded = 'true'
		} else {
			el.ariaExpanded = 'false'
		}
	}
	onShow(event) {
		super.onShow(event)
		this.setShowHeight()
		this.controlRefs.forEach(t => (t.ariaExpanded = 'true'))
	}
	onHide(event) {
		super.onHide(event)
		this.setHideHeight()
		// hacky way to enable exit transition, otherwise content instantly disappears
		if (event.detail.transition) {
			this.el.setAttribute('open', '')
			setTimeout(() => {
				this.el.removeAttribute('open')
			}, this.duration)
		} else {
			this.el.removeAttribute('open')
		}
		this.controlRefs.forEach(t => (t.ariaExpanded = 'false'))
	}
	setShowHeight() {
		this.el.style[this.useMinHeight ? 'minHeight' : 'height'] =
			`${(this.el.scrollHeight).toString()}px`
	}
	setHideHeight() {
		this.el.style[this.useMinHeight ? 'minHeight' : 'height'] =
			`${this.el.$('summary')?.offsetHeight ?? '0'}px`
	}
	connected() {
		super.connected()
		console.log(`connected accordion ${this.el.id}`)
		this.active ? this.setShowHeight() : this.setHideHeight()
		return this
	}
	disconnected() {
		super.disconnected()
		console.log(`disconnected accordion ${this.el.id}`)
	}
}
