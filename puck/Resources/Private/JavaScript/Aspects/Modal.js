import { $, $$, jsx } from '~/Utility/DomUtility'
import Showable from "~/Aspects/Showable"

/**
 * Modal ElementAspect
 * Should only be used on <dialog> elements
 */
export default class Modal extends Showable {
    static displayName = 'Modal'
    static attributes = {
        ...Showable.attributes,
        selfClickHide: true,
        escHide: true,
        appendTo: '[data-modal-container]',
    }
    onShow(event) {
        this.el.showModal()
        super.onShow(event);
        this.el.ariaModal = 'true'
        if (this.el.$('[data-autofocus]')) {
            this.el.$('[data-autofocus]').focus()
        } else {
            this.el.focus()
        }
    }
    onHide(event) {
        super.onHide(event)
        this.el.removeAttribute('aria-modal')
        if (event.detail.transition) {
            setTimeout(() => this.el.close(), this.duration)
        } else {
            this.el.close()
        }
    }

    initialize() {
        if (this.appendTo && !this.el.parentNode.matches(this.appendTo)) {
            $(this.appendTo)?.appendChild(this.el)
        }
    }

    connected() {
        if (this.el.tagName !== 'DIALOG') throw new Error('Modal ElementAspect should only be used on dialog elements')
        super.connected()
        // hacky way to make dialog exit animation work
        // firefox doesn't support display animation yet, so we have to disable the native dialog close
        this.handlerSet.add(this.el, 'cancel', event => event.preventDefault())
        return this
    }

    disconnected() {
        super.disconnected()
        this.handlerSet.clear()
    }
}