import Listeners from "./Listeners.js";

export default class FocusTrap {
    static events = {
        focusablesChanged: new Event('focusablesChanged')
    }
    constructor({ element, active = true }) {
        this.element = element
        this.active = active
        this.focusables = []
        this.mutationObserver = new MutationObserver(this.mutationCallback.bind(this))
        this.updateFocusables()
    }

    updateFocusables() {
        let focusables = [...this.element.querySelectorAll(
            'button, a[href], input:not([type="hidden"]), select, textarea, [draggable], [contenteditable], [tabindex], audio[controls] video[controls]'
        )]
        this.focusables = focusables.filter(f =>
            f.tabIndex !== -1 &&
            !f.disabled &&
            !f.hidden &&
            window.getComputedStyle(f,null).visibility !== 'hidden' &&
            window.getComputedStyle(f,null).display !== 'none')
    }

    getFirstFocusable() {
        return this.focusables[0] || this.element
    }
    getLastFocusable() {
        return this.focusables[this.focusables.length - 1] || this.element
    }

    mutationCallback(mutationsList, observer) {
        window.requestAnimationFrame(() => {
            this.updateFocusables()
            this.element.dispatchEvent(this.constructor.events.focusablesChanged)
        })
    }

    mount() {
        this.listeners = new Listeners()
        this.mutationObserver.observe(this.element, { childList: true, subtree: true })
        this.listeners.add(this.element, 'keydown', event => {
            if (!this.active) return
            if (event.key === 'Tab') {
                if (event.shiftKey) {
                    if (document.activeElement === this.getFirstFocusable() || document.activeElement === this.element) {
                        event.preventDefault()
                        this.getLastFocusable().focus()
                    }
                } else {
                    if (document.activeElement === this.getLastFocusable() || document.activeElement === this.element) {
                        event.preventDefault()
                        this.getFirstFocusable().focus()
                    }
                }
            }
        })
    }

    destroy() {
        this.mutationObserver.disconnect()
        this.listeners.destroy()
    }
}
