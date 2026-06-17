import Showable from '#/Controller/Showable.js'

/**
 * Dialog Controller
 * Should only be used on <dialog> elements
 */
export default class Dialog extends Showable {
	static props = {
		...Showable.props,
		escHide: true,
		focusOnShow: true,
		focusOutHide: true,
		outClickHide: true,
	}
	get element() { return /** @type {HTMLDialogElement} */ (super.element) }
	onShow(event) {
		this.element.show()
		super.onShow(event)
	}
	onHide(event) {
		super.onHide(event)
		if (event.detail.transition) {
			setTimeout(() => this.element.removeAttribute('open'), this.duration)
		} else {
			this.element.removeAttribute('open')
		}
	}
	connected() {
		if (this.element.tagName !== 'DIALOG') throw new Error('Dialog Aspect should only be used on dialog elements')
		super.connected()
		// hacky way to make dialog exit animation work
		// firefox doesn't support display animation yet, so we have to disable the native dialog close
		this.listeners.add(this.element, 'cancel', event => event.preventDefault())
	}
}
