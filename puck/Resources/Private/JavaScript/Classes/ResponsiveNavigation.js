import AbstractComponent from "./AbstractComponent.js";
import Modal from "./Modal.js";
import Listeners from "./Listeners.js";
import { getElement } from "../General/Functions.js";
import { MutationManager, ObserverManager, ResizeManager } from "./ObserverManager.js";

export default class ResponsiveNavigation extends AbstractComponent {
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
      observerElement: observerTarget ? getElement(observerTarget) : document.documentElement
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
    this.reset()
    this.replaceNavigationClass(this.responsiveClass, this.initialClass)
  }

  mountResponsive() {
    if (this.state === 'responsive') return
    this.state = 'responsive'
    this.reset()
    this.replaceNavigationClass(this.initialClass, this.responsiveClass)
    this.responsiveListeners = new Listeners()
    this.modal = new Modal(this.element, this.modalOptions).mount()
  }

  stateSwitch() {
    if (window.innerWidth < this.breakpoint) {
      this.mountResponsive()
    } else {
      this.mountDesktop()
    }
  }

  mount() {
    const observer = new MutationObserver((mutations, observer) => {
      for (let mutation of mutations) {
      }
    })
    observer.observe(this.element, { childList: false, subtree: false, attributes: true })
    MutationManager.addById('nav-mutations', this.element, (mutation, observer) => {
    }, { childList: false, subtree: false, attributes: true })
    this.state = 'initial'
    this.stateSwitch()
    ResizeManager.addById('responsive-navigation', this.observerElement, (entry, observer) => {
      this.stateSwitch()
    })
    return this
  }

  reset() {
    if (this.responsiveListeners) this.responsiveListeners.destroy()
    if (this.modal) {
      this.modal.toggleOff()
      this.modal.destroy()
      delete this.modal
    }
  }

  destroy() {
    this.reset()
    ResizeManager.remove('responsive-navigation')
  }
}
