import { $, $$, jsx } from '~/Utility/DomUtility'
import { ResizeManager } from "~/Service/ObserverCollector.js";
import Modal from "~/Controller/Modal"

export default class BreakpointModal extends Modal {
  static props = {
    ...Modal.props,
    duration: 300,
    appendToTarget: '',
    initClass: '',
    modalClass: '',
    breakpoint: 800,
  }

  replaceClass(from, to) {
    if (!from || !to) return
    this.el.className = this.el.className.replace(from, to)
    this.el.$$(`[class*="${from}"]`).forEach(el => {
      el.setAttribute('class', el.getAttribute('class').replace(from, to))
    })
  }

  disconnectModal() {
    if (this.state === 'initial') return
    this.state = 'initial'
    this.toggleOff()
    this.replaceClass(this.modalClass, this.initClass)
    super.disconnect()
  }

  connectModal() {
    if (this.state === 'modal') return
    this.state = 'modal'
    this.replaceClass(this.initClass, this.modalClass)
    super.connect()
  }

  stateSwitch() {
    if (window.innerWidth < this.breakpoint) {
      this.connectModal()
    } else {
      this.disconnectModal()
    }
  }

  connect() {
    this.state = 'initial'
    this.stateSwitch()
    ResizeManager.addById(`${this.el.id}-breakpoint-modal`, document.documentElement, this.stateSwitch.bind(this))
    return this
  }

  disconnect() {
    super.disconnect()
    ResizeManager.remove(`${this.el.id}-breakpoint-modal`)
  }
}

