import { $, $$, jsx } from '~/_jcores/Utility/DomUtility'
import Toggleable from "~/Cores/Toggleable"

/**
 * Modal Core
 * Should only be used on <dialog> elements
 */
export default class Modal extends Toggleable {
    static displayName = 'Modal'
    static attributes = {
        ...Toggleable.attributes,
        selfClickOff: true,
        escOff: true,
        appendTo: '[data-modal-container]',
    }
    toggleOn(transition= true) {
        this.el.showModal()
        super.toggleOn(transition);
        this.el.ariaModal = 'true'
        if (this.el.$('[autofocus]')) {
            this.el.$('[autofocus]').focus()
        } else {
            this.el.focus()
        }
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
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
        console.log(this.el.tagName)
        if (this.el.tagName !== 'DIALOG') throw new Error('Modal Core should only be used on dialog elements')
        super.connect()
        // hacky way to make dialog exit animation work
        // firefox doesnt support display animation yet, so we have to disable the native dialog close
        this.listeners.add(this.el, 'cancel', event => event.preventDefault())
        return this
    }

    disconnect() {
        super.disconnect()
        this.listeners.removeAll()
    }
}