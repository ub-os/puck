export default class FocusTrap {
    constructor({ node }) {
        this.node = node
        this.focusables = this.node.querySelectorAll('button, [href]:not([tabindex="-1"], input:not([tabindex="-1"], select, textarea, [tabindex]:not([tabindex="-1"])')
        this.firstFocusable = this.focusables[0]
        this.lastFocusable = this.focusables[this.focusables.length - 1]
    }
    mount() {
        this.node.addEventListener('keydown', event => {
            if (event.key === 'Tab') {
                if (event.shiftKey) {
                    if (document.activeElement === this.firstFocusable) {
                        event.preventDefault()
                        this.lastFocusable.focus()
                    }
                } else {
                    if (document.activeElement === this.lastFocusable) {
                        event.preventDefault()
                        this.firstFocusable.focus()
                    }
                }
            }
        })
    }
}
