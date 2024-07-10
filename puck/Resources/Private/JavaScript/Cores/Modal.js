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
        appendTo: '[data-modal-container]',
    }
    toggleOn(transition= true) {
        this.el.showModal()
        super.toggleOn(transition);
        this.el.ariaModal = 'true'
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.el.removeAttribute('aria-modal')
        this.el.close()
    }

    initialize() {
        if (this.appendTo && !this.el.parentNode.matches(this.appendTo)) {
            $(this.appendTo)?.appendChild(this.el)
        }
    }

    connect() {
        if (this.el.tagName !== 'DIALOG') throw new Error('Modal Core can only be used on dialog elements')
        super.connect()
        return this
    }

    disconnect() {
        super.disconnect()
        this.listeners.removeAll()
    }
}