import Modal from "~/Classes/Behaviors/Modal.js";
import { ResizeManager } from "~/Classes/ObserverManager.js";

export default class BreakpointModal extends Modal {
  static props = {
    ...Modal.props,
    clickDelay: 300,
    appendToTarget: '',
    initClass: '',
    modalClass: '',
    breakpoint: 800,
  }

  replaceClass(from, to) {
    if (!from || !to) return
    this.el.className = this.el.className.replace(from, to)
    this.el.querySelectorAll(`[class*="${from}"]`).forEach(el => {
      el.setAttribute('class', el.getAttribute('class').replace(from, to))
    })
  }

  mountInit() {
    if (this.state === 'initial') return
    this.state = 'initial'
    this.toggleOff()
    super.destroy()
    this.replaceClass(this.modalClass, this.initClass)
  }

  mountModal() {
    if (this.state === 'modal') return
    this.state = 'modal'
    super.mount()
    this.replaceClass(this.initClass, this.modalClass)
  }

  stateSwitch() {
    if (window.innerWidth < this.breakpoint) {
      this.mountModal()
    } else {
      this.mountInit()
    }
  }

  mount() {
    this.state = 'initial'
    this.stateSwitch()
    ResizeManager.addById(`${this.el.id}-breakpoint-modal`, document.documentElement, this.stateSwitch.bind(this))
    return this
  }

  destroy() {
    super.destroy()
    ResizeManager.remove(`${this.el.id}-breakpoint-modal`)
  }
}

