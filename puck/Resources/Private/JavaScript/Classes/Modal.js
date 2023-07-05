
import Toggleable from "./Toggleable"
import FocusTrap from "./FocusTrap.js";

class Modal extends Toggleable {
    constructor(target, { moveToModalContainer = true, toggleOffOnOutsideClick = true, ...options }) {
        super(target, { toggleOffOnOutsideClick, ...options })
        Object.assign(this, { moveToModalContainer })
        if (moveToModalContainer) {
            this.element = document.querySelector('[data-modal-container]').appendChild(this.element)
        }
        this.previousFocusable = null
        this.focusTrap = new FocusTrap({ element: this.element })
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        this.element.removeAttribute('aria-hidden')
        this.element.role = 'dialog'
        this.element.ariaModal = 'true'
        this.focusTrap.firstFocusable.focus()
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.element.ariaHidden = 'true'
        this.element.removeAttribute('aria-modal')
        this.element.removeAttribute('role')
        if (this.previousFocusable) this.previousFocusable.focus()
    }

    mount() {
        super.mount()
        this.focusTrap.mount()
        this.element.tabIndex = -1
        this.toggles.forEach(t => {
            if (!this.element.contains(t)) {
                t.addEventListener('click', () => {
                    this.previousFocusable = t
                })
            }
        })
        return this
    }
}

export default Modal

