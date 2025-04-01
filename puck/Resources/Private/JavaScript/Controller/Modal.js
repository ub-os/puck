import Showable from '~/Controller/Showable'

/**
 * Modal Aspect
 * Should only be used on <dialog> elements
 */
export default class Modal extends Showable {
	static props = {
		...Showable.props,
		selfClickHide: true,
		escHide: true,
		appendTo: '#modal-container',
		focusOnShow: true,
		pauseMediaOnHide: true,
		reloadIframeOnHide: true,
	}

	onShow(event) {
		this.element.showModal()
		super.onShow(event)
		this.element.ariaModal = 'true'
	}
	onHide(event) {
		super.onHide(event)
		this.element.removeAttribute('aria-modal')
		if (event.detail.transition) {
			setTimeout(() => this.element.close(), this.duration)
		} else {
			this.element.close()
		}
	}

	initialized() {
		if (this.appendTo) document.querySelector(this.appendTo)?.appendChild(this.element)
	}

	connected() {
		if (this.element.tagName !== 'DIALOG')
			throw new Error('Modal Aspect should only be used on dialog elements')
		super.connected()
		// hacky way to make dialog exit animation work
		// firefox doesn't support display animation yet, so we have to disable the native dialog close
		this.listeners.add(this.element, 'cancel', event => event.preventDefault())
	}

	disconnected() {
		super.disconnected()
		this.listeners.clear()
	}
}
