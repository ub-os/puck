import Toggleable from "./Toggleable"
import FocusTrap from "../FocusTrap.js";

export default class Modal extends Toggleable {
    static displayName = 'Modal'
    static props = {
        ...Toggleable.props,
        outsideClickOff: true,
        appendToTarget: '[data-modal-container]',
        backdropClass: 'l-modal__backdrop',
    }
    constructor() {
        super()
    }
    toggleOn(transition= true) {
        this.backdrop = document.createElement('div')
        this.backdrop.classList.add(this.backdropClass)
        this.parentNode.insertBefore(this.backdrop, this)
        super.toggleOn(transition);
        this.focusTrap.active = true
        this.removeAttribute('aria-hidden')
        this.role = 'dialog'
        this.ariaModal = 'true'
        this.focus()
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
        this.ariaHidden = 'true'
        this.removeAttribute('aria-modal')
        this.removeAttribute('role')
        if (this.lastUsedToggle) this.lastUsedToggle.focus()
    }

    mount() {
        if (this.appendToTarget && !this.parentNode.matches(this.appendToTarget)) {
            document.querySelector(this.appendToTarget).appendChild(this)
        }
        this.focusTrap = new FocusTrap({ element: this, active: this.active })
        this.backdrop = null
        super.mount()
        this.focusTrap.mount()
        this.tabIndex = -1
        this.listeners.add(this, 'focusablesChanged', event => {
            if (this.active) this.focusTrap.getFirstFocusable().focus()
        })
        return this
    }

    destroy() {
        super.destroy()
        this.removeAttribute('tabindex')
        if (this.focusTrap) {
            this.focusTrap.destroy()
        }
    }
}

window.customElements.define('pux-modal', Modal);