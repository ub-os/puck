import { $, $$, $id } from '~/Utility/DomUtility'
import Listeners from "~/Service/Listeners"
import { MutationManager } from "~/Service/ObserverCollector";
import Controller from "~/Application/Controller.js";

export default class FocusTrap extends Controller {
    static events = {
        focusablesChanged: new Event('focusables-changed'),
        updateFocusables: new Event('update-focusables')
    }
    static props = {
        active: true,
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

    getFirstFocusable() {
        return this.focusables[0] || this.el
    }
    getLastFocusable() {
        return this.focusables[this.focusables.length - 1] || this.el
    }

    connect() {
        this.focusables = []
        this.updateFocusables()
        this.listeners = new Listeners()
        
        if (this.updateFocusOn === 'mutation') {
            MutationManager.addById('focus-trap-' + this.el.id, this.el, (mutations, observer) => {
                window.requestAnimationFrame(() => {
                    this.updateFocusables()
                    this.el.dispatchEvent(this.constructor.events.focusablesChanged)
                })
            }, { childList: true, subtree: true, attributes: true })
        }
        
        if (this.updateFocusOn === 'event') {
            this.listeners.add(this.el, 'update-focusables', event => {
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

    disconnect() {
        this.listeners.destroy()
        MutationManager.remove('focus-trap-' + this.el.id)
    }
}
