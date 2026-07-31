import Showable from '#/Controller/Showable.js'

export default class Accordion extends Showable {
	static props = {
		...Showable.props,
		useMinHeight: false,
		pauseMediaOnHide: true,
	}
	controlTargetConnected(el) {
		super.controlTargetConnected(el)
		if (this.active) {
			el.ariaExpanded = 'true'
		} else {
			el.ariaExpanded = 'false'
		}
	}
	onShow(event) {
		super.onShow(event)
		this.setShowHeight()
		this.controlTargets.forEach(t => {
			t.ariaExpanded = 'true'
		})
	}
	onHide(event) {
		super.onHide(event)
		this.setHideHeight()
		// hacky way to enable exit transition, otherwise content instantly disappears
		if (event.detail.transition) {
			this.element.setAttribute('open', '')
			setTimeout(() => {
				this.element.removeAttribute('open')
			}, this.duration)
		} else {
			this.element.removeAttribute('open')
		}
		this.controlTargets.forEach(t => {
			t.ariaExpanded = 'false'
		})
	}
	setShowHeight() {
		this.element.style[this.useMinHeight ? 'minHeight' : 'height'] = `${(this.element.scrollHeight).toString()}px`
	}
	setHideHeight() {
		const style = window.getComputedStyle(this.element)
		const blockPadding = parseInt(style.getPropertyValue('padding-top')) + parseInt(style.getPropertyValue('padding-bottom'))
		this.element.style[this.useMinHeight ? 'minHeight' : 'height'] =
			`${(this.element.querySelector('summary')?.offsetHeight ?? '0') + blockPadding}px`
	}
	connected() {
		super.connected()
		this.active ? this.setShowHeight() : this.setHideHeight()
	}
	disconnected() {
		super.disconnected()
	}
}
