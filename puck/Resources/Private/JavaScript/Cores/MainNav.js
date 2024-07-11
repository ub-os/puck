import { $, $$, jsx } from '~/_jcores/Utility/DomUtility'
import { ResizeManager } from "~/Service/ObserverCollector"
import Core from "~/_jcores/Core"

/**
 * @property {Modal} modalCore
 */
export default class MainNav extends Core {
  static units = ['toggle']
  static attributes = {
    breakpoint: 800,
    initClass: 'l-main-nav--init',
    modalClass: 'l-main-nav--modal',
    modalHtmxSwapDelay: 0
  }
  static injects = ['modal']
  replaceClass(from, to) {
    if (!from || !to) return
    this.el.className = this.el.className.replace(from, to)
    this.el.$$(`[class*="${from}"]`).forEach(el => {
      el.setAttribute('class', el.getAttribute('class').replace(from, to))
    })
  }

  stateSwitch() {
    if (this.modalCore.asleep && (window.innerWidth < this.breakpoint)) {
      this.replaceClass(this.initClass, this.modalClass)
      this.modalCore.asleep = false
      this.dispatch('update-focusables')
      if (this.modalHtmxSwapDelay > 0) {
        this.el.setAttribute('data-hx-swap', `outerHTML swap:${this.modalHtmxSwapDelay}s`)
        window.htmx.process(this.el)
      }
    } else
    if (!this.modalCore.asleep && (window.innerWidth >= this.breakpoint)) {
      this.modalCore.toggleOff(false)
      this.replaceClass(this.modalClass, this.initClass)
      this.modalCore.asleep = true
      if (this.modalHtmxSwapDelay > 0) {
        this.el.removeAttribute('data-hx-swap')
        window.htmx.process(this.el)
      }
    }
  }

  initialize() {
    this.modalCore.asleep = true
    this.modalCore.el.classList.remove(this.modalCore.deactivatingClass)
  }

  connect() {
    window.requestAnimationFrame(() => {
      this.stateSwitch()
      window.requestAnimationFrame(() => {
        ResizeManager.addById(`main-nav-${this.el.id}`, document.body, this.stateSwitch.bind(this))
      })
    })
    this.listeners.add(this.el, 'click', e => {
      if (e.target.closest('a') && !this.modalCore.asleep) {
        this.modalCore.toggleOff(true)
      }
    })
    return this
  }

  disconnect() {
    this.listeners.removeAll()
    ResizeManager.remove(`main-nav-${this.el.id}`)
  }
}
