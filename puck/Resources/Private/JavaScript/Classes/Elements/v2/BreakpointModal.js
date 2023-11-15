import Modal from "./Modal";

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
    this.observerElement = document.documentElement
    this.state = 'initial'
    this.stateSwitch()
    this.resizeObserver = new ResizeObserver(entries => {
      this.stateSwitch()
    })
    this.resizeObserver.observe(this.observerElement)
    return this
  }

  destroy() {
    super.destroy()
    this.resizeObserver.unobserve(this.observerElement)
  }
}

