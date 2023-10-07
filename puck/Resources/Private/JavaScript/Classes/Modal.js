
import Toggleable from "./Toggleable"
import FocusTrap from "./FocusTrap.js";

export default class Modal extends Toggleable {
    static displayName = 'Modal'
    constructor(target, { moveToModalContainer = true, toggleOffOnOutsideClick = true, ...options }) {
        super(target, { toggleOffOnOutsideClick, ...options })
        Object.assign(this, { moveToModalContainer })
        if (moveToModalContainer) {
            this.element = document.querySelector('[data-modal-container]').appendChild(this.element)
        }
        this.previousFocusable = null
        this.focusTrap = new FocusTrap({ element: this.element, active: this.active })
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        this.focusTrap.active = true
        this.element.removeAttribute('aria-hidden')
        this.element.role = 'dialog'
        this.element.ariaModal = 'true'
        this.focusTrap.getFirstFocusable().focus()
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.focusTrap.active = false
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
        this.element.addEventListener('focusablesChanged', () => {
            if (this.active) this.focusTrap.getFirstFocusable().focus()
        })
        return this
    }
}

