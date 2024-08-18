import { $, $$, jsx } from '~/Utility/DomUtility'
import Showable from "~/Cores/Showable"

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
    show({ transition = true }) {
        this.el.showModal()
        super.show({ transition });
        this.el.ariaModal = 'true'
        if (this.el.$('[data-autofocus]')) {
            this.el.$('[data-autofocus]').focus()
        } else {
            this.el.focus()
        }
    }
    hide({ transition = true, changeUrlHash = true }) {
        super.hide({ transition, changeUrlHash })
        this.el.removeAttribute('aria-modal')
        if (transition) {
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

    connect() {
        if (this.el.tagName !== 'DIALOG') throw new Error('Modal ElementAspect should only be used on dialog elements')
        super.connect()
        // hacky way to make dialog exit animation work
        // firefox doesn't support display animation yet, so we have to disable the native dialog close
        this.handlerSet.add(this.el, 'cancel', event => event.preventDefault())
        return this
    }

    disconnect() {
        super.disconnect()
        this.handlerSet.clear()
    }
}