export default class FocusTrap {
    static events = {
        focusablesChanged: new Event('focusablesChanged')
    }
    constructor({ element }) {
        this.element = element
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
        return this.focusables[0]
    }
    getLastFocusable() {
        return this.focusables[this.focusables.length - 1]
    }

    mutationCallback(mutationsList, observer) {
        this.updateFocusables()
        this.element.dispatchEvent(this.constructor.events.focusablesChanged)
    }

    mount() {
        this.mutationObserver.observe(this.element, { childList: true, subtree: true })
        this.element.addEventListener('keydown', event => {
            if (event.key === 'Tab') {
                if (event.shiftKey) {
                    if (document.activeElement === this.getFirstFocusable()) {
                        event.preventDefault()
                        this.getLastFocusable().focus()
                    }
                } else {
                    if (document.activeElement === this.getLastFocusable()) {
                        event.preventDefault()
                        this.getFirstFocusable().focus()
                    }
                }
            }
        })
    }
}
