import { $, $$, jsx } from '../../../General/Aliases';
import Toggleable from "./PuxToggleable"
import FocusTrap from "../../FocusTrap.js";

export default class Modal extends Toggleable {
    static displayName = 'Modal'
    static props = {
        ...Toggleable.props,
        outsideClickOff: true,
        appendToTarget: '[data-modal-container]',
        backdropClass: 'l-modal__backdrop',
    }
    testInstancePropModal = ''
    toggleOn(transition= true) {
        this.backdrop = <div class={this.backdropClass}></div>
        this.parentNode.insertBefore(this.backdrop, this.el)
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
            }, this.clickDelay)
        }
        super.toggleOff(transition, changeUrlHash)
        this.focusTrap.active = false
        this.el.ariaHidden = 'true'
        this.el.removeAttribute('aria-modal')
        this.el.removeAttribute('role')
        if (this.lastUsedToggle) this.lastUsedToggle.focus()
    }
    mount() {
        if (this.appendToTarget && !this.el.parentNode.matches(this.appendToTarget)) {
            document.querySelector(this.appendToTarget).appendChild(this.el)
        }
        this.focusTrap = new FocusTrap({ element: this.el, active: this.active })
        this.backdrop = null
        super.mount()
        this.focusTrap.mount()
        this.el.tabIndex = -1
        this.listeners.add(this, 'focusablesChanged', event => {
            if (this.active) this.focusTrap.getFirstFocusable().focus()
        })
        return this
    }

    destroy() {
        super.destroy()
        this.el.removeAttribute('tabindex')
        if (this.focusTrap) {
            this.focusTrap.destroy()
        }
    }
}