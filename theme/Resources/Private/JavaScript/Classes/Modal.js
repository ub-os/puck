
import {Toggleable, toggleEvents} from "./Toggleable"
import FocusTrap from "./FocusTrap.js";

class Modal extends Toggleable {
    constructor(target, { toggleOffOnOutsideClick = true, ...options }) {
        super(target, { toggleOffOnOutsideClick, ...options })
        this.previousFocusable = null
        this.focusTrap = new FocusTrap({ node: this.node })
    }
    toggleOn() {
        super.toggleOn();
        this.node.removeAttribute('aria-hidden')
        this.node.role = 'dialog'
        this.node.ariaModal = 'true'
        this.focusTrap.firstFocusable.focus()
    }
    toggleOff() {
        super.toggleOff()
        this.node.ariaHidden = 'true'
        this.node.removeAttribute('aria-modal')
        this.node.removeAttribute('role')
        if (this.previousFocusable) this.previousFocusable.focus()
    }
    mount() {
        super.mount()
        this.focusTrap.mount()
        this.node.tabIndex = -1
        this.toggles.forEach(t => {
            if (!this.node.contains(t)) {
                t.addEventListener('click', () => {
                    this.previousFocusable = t
                })
            }
        })
        return this
    }
}

export default Modal
