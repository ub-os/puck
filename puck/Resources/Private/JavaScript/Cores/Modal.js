import { $, $$, jsx } from '~/_htmc/Utility/DomUtility'
import Toggleable from "~/Cores/Toggleable"
import FocusTrap from "~/Cores/FocusTrap"

/**
 * @property {FocusTrap} focusTrapCore
 */
export default class Modal extends Toggleable {
    static displayName = 'Modal'
    static attributes = {
        ...Toggleable.attributes,
        outClickOff: true,
        appendTo: '[data-modal-container]',
        backdropClass: 'l-modal__backdrop',
    }
    static injects = ['focus-trap']
    toggleOn(transition= true) {
        this.backdropEl = <div class={this.backdropClass} data-render-excluded></div>
        this.el.parentNode.insertBefore(this.backdropEl, this.el)
        super.toggleOn(transition);
        this.focusTrapCore.active = true
        this.el.removeAttribute('aria-hidden')
        this.el.role = 'dialog'
        this.el.ariaModal = 'true'
        this.el.focus()
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        setTimeout(() => {
            this.backdropEl?.remove()
            this.backdropEl = null
        }, this.duration)
        super.toggleOff(transition, changeUrlHash)
        this.focusTrapCore.active = false
        this.el.ariaHidden = 'true'
        this.el.removeAttribute('aria-modal')
        this.el.removeAttribute('role')
        if (this.lastUsedToggle) this.lastUsedToggle.focus()
    }

    initialize() {
        if (this.appendTo && !this.el.parentNode.matches(this.appendTo)) {
            $(this.appendTo)?.appendChild(this.el)
        }
    }

    connect() {
        super.connect()
        this.el.tabIndex = -1
        this.listeners.add(this.el, 'focusables-changed', event => {
            if (this.active) this.focusTrapCore.firstFocusable.focus()
        })
        return this
    }

    disconnect() {
        super.disconnect()
        this.listeners.removeAll()
        this.el.removeAttribute('tabindex')
        this.el.removeAttribute('aria-hidden')
    }
}