import { $, $$, $id } from '~/Utility/DomUtility'
import { ElementAspect } from "~/_jcores"

export default class FocusTrap extends ElementAspect {
    static attributes = {
        active: false,
        updateFocusOn: 'event' // event, mutation
    }

    updateFocusables() {
        let focusables = [...this.el.$$(
            'button, a[href], input:not([type="hidden"]), select, textarea, [draggable], [contenteditable], [tabindex], audio[controls] video[controls]'
        )]
        this.focusables = focusables.filter(f =>
            f.tabIndex !== -1 &&
            !f.disabled &&
            !f.hidden &&
            f.checkVisibility()
        )
    }

    get firstFocusable() {
        return this.focusables[0] || this.el
    }
    get lastFocusable() {
        return this.focusables[this.focusables.length - 1] || this.el
    }

    connected() {
        this.focusables = []
        this.updateFocusables()
        if (this.updateFocusOn === 'mutation') {
            this.mutationObserver = new MutationObserver((mutations, observer) => {
                window.requestAnimationFrame(() => {
                    this.updateFocusables()
                    this.dispatch('focusables-changed')
                })
            })
            this.mutationObserver.observe(this.el, { childList: true, subtree: true, attributes: true })
        }


        if (this.updateFocusOn === 'event') {
            this.on('update-focusables', event => {
                this.updateFocusables()
                this.dispatch('focusables-changed')
            })
        }
        
        this.on('keydown', event => {
            if (!this.active) return
            if (event.key === 'Tab') {
                if (event.shiftKey) {
                    if (document.activeElement === this.firstFocusable || document.activeElement === this.el) {
                        event.preventDefault()
                        this.lastFocusable.focus()
                    }
                } else {
                    if (document.activeElement === this.lastFocusable || document.activeElement === this.el) {
                        event.preventDefault()
                        this.firstFocusable.focus()
                    }
                }
            }
        })
    }

    disconnected() {
        this.mutationObserver?.disconnect()
    }
}
