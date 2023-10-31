import { jsx } from '../General/Aliases';
import Toggleable from "./Toggleable"
import FocusTrap from "./FocusTrap.js";
import Listeners from "./Listeners.js";

export default class Modal extends Toggleable {
    static displayName = 'Modal'
    constructor(target, {
        moveToModalContainer = true,
        toggleOffOnOutsideClick = true,
        backdropClass = 'l-modal__backdrop',
        ...options }) {
        super(target, { toggleOffOnOutsideClick, ...options })
        Object.assign(this, { moveToModalContainer, backdropClass })
        if (moveToModalContainer) {
            this.element = document.querySelector('[data-modal-container]').appendChild(this.element)
        }
        this.previousFocusable = null
        this.focusTrap = new FocusTrap({ element: this.element, active: this.active })
        this.backdrop = null
    }
    toggleOn(transition= true) {
        this.backdrop = ( <div class={this.backdropClass} data-turbo-temporary></div> )
        this.element.parentNode.insertBefore(this.backdrop, this.element)
        super.toggleOn(transition);
        this.focusTrap.active = true
        this.element.removeAttribute('aria-hidden')
        this.element.role = 'dialog'
        this.element.ariaModal = 'true'
        this.element.focus()
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
                this.listeners.add(t, 'click', () => {
                    this.previousFocusable = t
                })
            }
        })
        this.listeners.add(this.element, 'focusablesChanged', event => {
            //if (this.active) this.element.focus()
        })
        return this
    }

    destroy() {
        super.destroy()
        this.focusTrap.destroy()
        this.listeners.destroy()
        delete this.focusTrap
        this.element.removeAttribute('tabindex')
        this.element.removeAttribute('aria-hidden')
        this.element.removeAttribute('aria-modal')
        this.element.removeAttribute('role')
    }
}