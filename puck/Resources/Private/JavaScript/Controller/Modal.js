import { $, $$, jsx } from '~/Utility/DomUtility'
import Toggleable from "~/Controller/Toggleable"
import FocusTrap from "~/Controller/FocusTrap"

export default class Modal extends Toggleable {
    static displayName = 'Modal'
    static props = {
        ...Toggleable.props,
        outClickOff: true,
        appendToTarget: '[data-modal-container]',
        backdropClass: 'l-modal__backdrop',
    }
    focusTrap = {}
    toggleOn(transition= true) {
        this.backdrop = <div class={this.backdropClass} data-turbo-render-excluded></div>
        this.el.parentNode.insertBefore(this.backdrop, this.el)
        super.toggleOn(transition);
        this.focusTrap.active = true
        this.el.removeAttribute('aria-hidden')
        this.el.role = 'dialog'
        this.el.ariaModal = 'true'
        this.el.focus()
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        if (this.backdrop) {
            setTimeout(() => {
                this.backdrop.remove()
                this.backdrop = null
            }, this.duration)
        }
        super.toggleOff(transition, changeUrlHash)
        this.focusTrap.active = false
        this.el.ariaHidden = 'true'
        this.el.removeAttribute('aria-modal')
        this.el.removeAttribute('role')
        if (this.lastUsedToggle) this.lastUsedToggle.focus()
    }

    connect() {
        if (this.appendToTarget && !this.el.parentNode.matches(this.appendToTarget)) {
            $(this.appendToTarget).appendChild(this.el)
            return
        }
        this.focusTrap = new FocusTrap(this.el, { active: this.active })
        this.backdrop = null
        super.connect()
        this.focusTrap.connect()
        this.el.tabIndex = -1
        this.listeners.add(this.el, 'focusables-changed', event => {
            if (this.active) this.focusTrap.getFirstFocusable().focus()
        })
        return this
    }

    disconnect() {
        super.disconnect()
        this.el.removeAttribute('tabindex')
        if (this.focusTrap.disconnect) {
            this.focusTrap.disconnect()
        }
    }
}