import { $, $$, jsx } from '~/Utility/DomUtility'
import { ResizeManager } from "~/Service/ObserverCollector.js";
import Controller from "~/Application/Controller"
import Modal from "~/Controller/Modal"

/**
 * @property {Modal} modalController
 */
export default class MainNav extends Controller {
  static targets = ['toggle']
  static props = {
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
    if (this.state !== 'modal' && (window.innerWidth < this.breakpoint)) {
      this.state = 'modal'
      this.replaceClass(this.initClass, this.modalClass)
      this.modalController.connect()
      this.el.dispatchEvent(new Event('update-focusables'))
    }
    if (this.state === 'modal' && (window.innerWidth >= this.breakpoint)) {
      this.state = ''
      this.replaceClass(this.modalClass, this.initClass)
      this.modalController.disconnect()
    }
  }

  initialize() {
    this.state = ''
  }

  connect() {
    window.requestAnimationFrame(() => {
      if (window.innerWidth < this.breakpoint) {
        this.modalController.disconnect()
      }
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
