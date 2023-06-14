import {getElement} from '../General/Functions'
import AbstractComponent from "./AbstractComponent.js";

export default class ScrollSensitive extends AbstractComponent {
  static events = {
    addScrollClass: new Event('addScrollClass'),
    removeScrollClass: new Event('removeScrollClass')
  }
  static defaultObserverOptions = {
    root: null,
    rootMargin: '0px 0px 0px 0px',
    threshold: [0,1],
  }
  static createIntersectionObserver = (options = this.defaultObserverOptions) => {
    const observer =  new IntersectionObserver(
        (entries, observer) => {
          const top = parseInt(options.rootMargin.split('px')[0]) * -1
          entries.forEach(e => {
            if (e.boundingClientRect.top > top) {
              // do things if below
              e.target.dispatchEvent(this.events.removeScrollClass)
            } else {
              // do things if above
              e.target.dispatchEvent(this.events.addScrollClass)
            }
          })
        },
        options)
    return observer
  }
  static defaultObserver = this.createIntersectionObserver()

  constructor(target, {
    scrollClass = '--scroll',
    observerRoot,
    observerRootMargin,
    observerThreshold,
    observationTarget,
    cloneElementToRetainFlow = false,
    cloneClass = '-scroll-sense-clone',
    scrollTop = 0,
    scrollParent = window,
    ...options})
  {
    //
    super(target)
    Object.assign(this, {
      scrollClass, scrollTop, observerRoot, observerRootMargin, observerThreshold,
      observationTarget, cloneElementToRetainFlow, scrollParent, ...options })
    this.observedElement = observationTarget ? getElement(observationTarget, 'ScrollSensitive') : this.element
    if (cloneElementToRetainFlow) {
      const clone = this.element.cloneNode(true)
      clone.classList.add(cloneClass)
      this.element.parentElement.insertBefore(clone, this.element)
      this.element = clone
    }
  }
  mount() {
    if (this.scrollTop) {
      this.scrollParent.addEventListener('scroll', e => {
        if ((this.scrollParent == window && document.documentElement.scrollTop < this.scrollTop) || (this.scrollParent != window && this.scrollParent.scrollTop < this.scrollTop)) {
          this.element.classList.remove(this.scrollClass);
        } else  {
          this.element.classList.add(this.scrollClass);
        }
      })
      return this
    }
    if (this.observerRoot || this.observerRootMargin || this.observerThreshold) {
      this.constructor.createIntersectionObserver({
        root: this.observerRoot ? getElement(this.observerRoot) : null,
        rootMargin: this.observerRootMargin || '0px 0px 0px 0px',
        threshold: this.observerThreshold || [0,1]
      }).observe(this.observedElement)
    } else {
      this.constructor.defaultObserver.observe(this.observedElement)
    }
    this.observedElement.addEventListener('addScrollClass', () => {
      this.element.classList.add(this.scrollClass)
    })
    this.observedElement.addEventListener('removeScrollClass', () => {
      this.element.classList.remove(this.scrollClass)
    })
    return this
  }
}