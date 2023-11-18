import { $, $$, id$ } from "~/General/Aliases"
import Listeners from "~/Classes/Listeners"
import { MutationManager } from "~/Classes/ObserverManager";
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior.js";

export default class FocusTrap extends AbstractBehavior {
    static events = {
        focusablesChanged: new Event('focusablesChanged'),
        updateFocusables: new Event('updateFocusables')
    }
    props = {
        active: true,
        updateFocusOnEvent: true,
        updateFocusOnMutation: false,
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

    getFirstFocusable() {
        return this.focusables[0] || this.el
    }
    getLastFocusable() {
        return this.focusables[this.focusables.length - 1] || this.el
    }

    mount() {
        this.focusables = []
        this.updateFocusables()
        this.listeners = new Listeners()
        
        if (this.updateFocusOnMutation) {
            MutationManager.addById('focus-trap-' + this.el.id, this.el, (mutations, observer) => {
                window.requestAnimationFrame(() => {
                    this.updateFocusables()
                    this.el.dispatchEvent(this.constructor.events.focusablesChanged)
                })
            }, { childList: true, subtree: true, attributes: true })
        }
        
        if (this.updateFocusOnEvent) {
            this.listeners.add(this.el, 'updateFocusables', event => {
                this.updateFocusables()
                this.el.dispatchEvent(this.constructor.events.focusablesChanged)
            })
        }
        
        this.listeners.add(this.el, 'keydown', event => {
            if (!this.active) return
            if (event.key === 'Tab') {
                if (event.shiftKey) {
                    if (document.activeElement === this.getFirstFocusable() || document.activeElement === this.el) {
                        event.preventDefault()
                        this.getLastFocusable().focus()
                    }
                } else {
                    if (document.activeElement === this.getLastFocusable() || document.activeElement === this.el) {
                        event.preventDefault()
                        this.getFirstFocusable().focus()
                    }
                }
            }
        })
    }

    destroy() {
        this.listeners.destroy()
        MutationManager.remove('focus-trap-' + this.el.id)
    }
}
