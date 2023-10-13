import AbstractComponent from "./AbstractComponent.js";
import Modal from "./Modal.js";
import Listeners from "./Listeners.js";
import { getElement } from "../General/Functions.js";

export default class ResponsiveNavigation extends AbstractComponent{
  constructor(target, {
      initialClass = '',
      responsiveClass = '',
      replaceClass = true,
      breakpoint = 800,
      modalOptions = {},
      observerTarget = null,
      ...options
  }) {
    super(target, options)
    Object.assign(this, {
      breakpoint, initialClass, responsiveClass, replaceClass,
      modalOptions: {
        moveToModalContainer: false,
        clickDelay: 300,
        ...modalOptions
      },
      observerElement: observerTarget ? getElement(observerTarget) : document.body
    })
  }

  replaceNavigationClass(from, to) {
    if (!this.replaceClass || !from || !to) return
    this.element.className = this.element.className.replace(from, to)
    this.element.querySelectorAll(`[class*="${from}"]`).forEach(el => {
      el.className = el.className.replace(from, to)
    })
  }

  mountDesktop() {
    if (this.state === 'desktop') return
    this.state = 'desktop'
    this.destroy()
    this.replaceNavigationClass(this.responsiveClass, this.initialClass)
  }

  mountResponsive() {
    if (this.state === 'responsive') return
    this.state = 'responsive'
    this.destroy()
    this.replaceNavigationClass(this.initialClass, this.responsiveClass)
    this.responsiveListeners = new Listeners()
    this.modal = new Modal(this.element, this.modalOptions).mount()
  }

  stateSwitch(windowWidth) {
    if (windowWidth < this.breakpoint) {
      this.mountResponsive()
    } else {
      this.mountDesktop()
    }
  }

  mount() {
    this.state = 'initial'
    this.stateSwitch(window.innerWidth)
    this.resizeObserver = new ResizeObserver(entries => {
      entries.forEach(entry => {
        this.stateSwitch(entry.contentRect.width)
      })
    })
    this.resizeObserver.observe(this.observerElement)
    return this
  }

  destroy() {
    if (this.responsiveListeners) this.responsiveListeners.destroy()
    if (this.modal) {
      this.modal.toggleOff()
      this.modal.destroy()
      delete this.modal
    }
  }

}
