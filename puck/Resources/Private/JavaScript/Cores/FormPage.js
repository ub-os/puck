import { $, $$, $id, scrollTo, tryViewTransition } from '~/Utility/DomUtility'
import Showable from '~/Cores/Showable'

export default class FormPage extends Showable {
    static displayName = 'FormPage'
    static attributes = {
        ...Showable.attributes,
        escHide: false,
        exclusiveGroup: true
    }
    formEl = null
    show(
        {   transition = true,
            validate = false,
            scrollIntoView = false
        } = {}) {
        if (validate && this.formEl) {
            let valid = true
            this.formEl.$$('[data-form-page\\.active]').forEach(page => {
                page.$$('input, select, textarea').forEach(input => {
                    if (!input.reportValidity()) valid = false
                })
            })
            if (!valid) return
        }
        tryViewTransition(() => super.show({ transition }))
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


