
import {Toggleable, toggleEvents} from "./Toggleable"
import FocusTrap from "./FocusTrap.js";

class Modal extends Toggleable {
    constructor(target, { moveToModalContainer = true, toggleOffOnOutsideClick = true, ...options }) {
        super(target, { toggleOffOnOutsideClick, ...options })
        Object.assign(this, { moveToModalContainer })
        if (moveToModalContainer) {
            this.node = document.querySelector('[data-modal-container]').appendChild(this.node)
        }
        this.previousFocusable = null
        this.focusTrap = new FocusTrap({ node: this.node })
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        this.node.removeAttribute('aria-hidden')
        this.node.role = 'dialog'
        this.node.ariaModal = 'true'
        this.focusTrap.firstFocusable.focus()
    }
    toggleOff(transition= true) {
        super.toggleOff(transition)
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

