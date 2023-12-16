import { $, $$, jsx } from '~/Utility/DomUtility'
import Toggleable from "~/Controller/Toggleable"
import FocusTrap from "~/Controller/FocusTrap"

/**
 * @property {FocusTrap} focusTrapController
 */
export default class Modal extends Toggleable {
    static displayName = 'Modal'
    static props = {
        ...Toggleable.props,
        outClickOff: true,
        appendTo: '[data-modal-container]',
        backdropClass: 'l-modal__backdrop',
    }
    static injects = ['focus-trap']
    toggleOn(transition= true) {
        console.log('toggle on modal')
        this.backdropEl = <div class={this.backdropClass} data-render-excluded></div>
        this.el.parentNode.insertBefore(this.backdropEl, this.el)
        super.toggleOn(transition);
        this.focusTrapController.active = true
        this.el.removeAttribute('aria-hidden')
        this.el.role = 'dialog'
        this.el.ariaModal = 'true'
        this.el.focus()
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        console.log('toggle off modal')
        setTimeout(() => {
            this.backdropEl?.remove()
            this.backdropEl = null
        }, this.duration)
        super.toggleOff(transition, changeUrlHash)
        this.focusTrapController.active = false
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
            if (this.active) this.focusTrapController.firstFocusable.focus()
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