import Showable from '~/Aspects/Showable'
import { $, $$, $id, scrollTo, tryViewTransition } from '~/Utility/DomUtility'

export default class FormPage extends Showable {
	static attributes = {
		...Showable.attributes,
		escHide: false,
		exclusiveGroup: true,
	}
	formEl = null
	show({
		transition = true,
		trigger = '',
		validate = false,
		scrollIntoView = false,
	} = {}) {
		this.dispatch('show', {
			detail: { transition, trigger, validate, scrollIntoView },
		})
	}
	onShow(event) {
		if (event.detail.validate && this.formEl) {
			let valid = true
			this.formEl.$$('[data-form-page\\.active]').forEach(page => {
				page.$$('input, select, textarea').forEach(input => {
					if (!input.reportValidity()) valid = false
				})
			})
			if (!valid) return
		}
		tryViewTransition(() => super.onShow(event))
		if (event.detail.scrollIntoView) {
			window.requestAnimationFrame(() => {
				this.el.scrollIntoView({ behavior: 'smooth', block: 'start' })
			})
		}
	}
	connected() {
		this.formEl = this.el.closest('form')
		if (!this.formEl) {
			console.warn(`No form element found for form page ${this.el.id}`)
		}
		super.connected()
		return this
	}
}
