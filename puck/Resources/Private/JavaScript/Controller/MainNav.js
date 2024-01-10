import { $, $$, jsx } from '~/_Stim/Utility/DomUtility'
import { ResizeManager } from "~/Service/ObserverCollector"
import Controller from "~/_Stim/Controller"

/**
 * @property {Modal} modalController
 */
export default class MainNav extends Controller {
  static targets = ['toggle']
  static attributes = {
    breakpoint: 800,
    initClass: 'l-main-nav--init',
    modalClass: 'l-main-nav--modal',
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
    if (this.modalController.asleep && (window.innerWidth < this.breakpoint)) {
      this.replaceClass(this.initClass, this.modalClass)
      this.modalController.asleep = false
      this.el.dispatchEvent(new Event('update-focusables'))
    } else
    if (!this.modalController.asleep && (window.innerWidth >= this.breakpoint)) {
      this.replaceClass(this.modalClass, this.initClass)
      this.modalController.asleep = true
    }
  }

  initialize() {
    this.modalController.asleep = true
  }

  connect() {
    window.requestAnimationFrame(() => {
      this.stateSwitch()
      window.requestAnimationFrame(() => {
        ResizeManager.addById(`main-nav-${this.el.id}`, document.body, this.stateSwitch.bind(this))
      })
    })
    return this
  }

  disconnect() {
    ResizeManager.remove(`main-nav-${this.el.id}`)
  }
}
