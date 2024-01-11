import { $, $$, $id } from '~/_Stim/Utility/DomUtility'
import { MutationManager } from "~/Service/ObserverCollector";
import Controller from "~/_Stim/Controller"

export default class FocusTrap extends Controller {
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
            window.getComputedStyle(f,null).visibility !== 'hidden' &&
            window.getComputedStyle(f,null).display !== 'none')
    }

    get firstFocusable() {
        return this.focusables[0] || this.el
    }
    get lastFocusable() {
        return this.focusables[this.focusables.length - 1] || this.el
    }

    connect() {
        this.focusables = []
        this.updateFocusables()
        if (this.updateFocusOn === 'mutation') {
            MutationManager.addById('focus-trap-' + this.el.id, this.el, (mutations, observer) => {
                window.requestAnimationFrame(() => {
                    this.updateFocusables()
                    this.dispatch('focusables-changed')
                })
            }, { childList: true, subtree: true, attributes: true })
        }
        
        if (this.updateFocusOn === 'event') {
            this.listeners.add(this.el, 'update-focusables', event => {
                this.updateFocusables()
                this.dispatch('focusables-changed')
            })
        }
        
        this.listeners.add(this.el, 'keydown', event => {
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

    disconnect() {
        this.listeners.destroy()
        MutationManager.remove('focus-trap-' + this.el.id)
    }
}
