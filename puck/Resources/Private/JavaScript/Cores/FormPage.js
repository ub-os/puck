import { $, $$, $id, scrollTo, tryViewTransition } from '~/_jcores/Utility/DomUtility'
import Toggleable from '~/Cores/Toggleable'

export default class FormPage extends Toggleable {
    static displayName = 'FormPage'
    static attributes = {
        ...Toggleable.attributes,
        escOff: false,
        exclusiveGroup: true
    }
    static triggerables = {
        toggle: {
            transition: true,
            validate: false,
            scrollIntoView: false
        }
    }
    formEl = null
    toggle(event, { transition, validate, scrollIntoView } = {}) {
        if (validate && this.formEl) {
            let valid = true
            this.formEl.$$('[data-form-page\\:active]').forEach(page => {
                page.$$('input, select, textarea').forEach(input => {
                    if (!input.reportValidity()) valid = false
                })
            })
            if (!valid) return
        }
        tryViewTransition(() => super.toggle(event, { transition }))
        if (scrollIntoView) {
            window.requestAnimationFrame(() => {
                this.el.scrollIntoView({ behavior: 'smooth', block: 'start' })
            })
        }
    }
    connect() {
        this.formEl = this.el.closest('form')
        if (!this.formEl) {
            console.warn(`No form element found for form page ${this.el.id}`)
        }
        super.connect()
        return this
    }
}


