import { getElement } from '../General/Functions'
import AbstractComponent from "./AbstractComponent";
import Listeners from "./Listeners.js";
import { IntersectionManager } from "./ObserverManager.js";

export default class ScrollSensitive extends AbstractComponent {

  constructor(target, {
    scrollClass = '--scroll',
    classes = {},
    observer = {},
    cloneElementToRetainFlow = false,
    cloneClass = '-scroll-sense-clone',
    scrollTop = 0,
    scrollParent = window,
    ...options})
  {
    super(target)
    Object.assign(this, { scrollClass, scrollTop, cloneElementToRetainFlow, scrollParent, ...options })

    this.observer = {
      root: null,
      rootMargin: '0px 0px 0px 0px',
      threshold: [0,1],
      target : null,
      ...observer
    }
    this.classes = {
      topInsideView: '--top-inside-view',
      topAboveView: '--top-above-view',
      topBelowView: '--top-below-view',
      bottomInsideView: '--bottom-inside-view',
      bottomAboveView: '--bottom-above-view',
      bottomBelowView: '--bottom-below-view',
      ...classes
    }
    this.observedElement = this.observer.target ? getElement(this.observer.target, 'ScrollSensitive') : this.element
    if (cloneElementToRetainFlow) {
      const clone = this.node.cloneNode(true)
      clone.classList.add(cloneClass)
      this.element.parentNode.insertBefore(clone, this.element)
      this.element = clone
    }
  }

  observerCallback(entry, observer) {
    for (let side of ['top', 'bottom']) {
      if (entry.boundingClientRect[side] > entry.rootBounds.top) {
        this.element.classList.remove(this.classes[side+'AboveView'])
        if (entry.boundingClientRect[side] < entry.rootBounds.bottom) {
          this.element.classList.add(this.classes[side+'InsideView'])
          this.element.classList.remove(this.classes[side+'BelowView'])
        } else {
          this.element.classList.add(this.classes[side+'BelowView'])
          this.element.classList.remove(this.classes[side+'InsideView'])
        }
      } else {
        this.element.classList.add(this.classes[side+'AboveView'])
        this.element.classList.remove(this.classes[side+'InsideView'])
        this.element.classList.remove(this.classes[side+'BelowView'])
      }
    }
  }
  
  mount() {
    this.listeners = new Listeners()
    if (this.scrollTop) {
      this.listeners.add(this.scrollParent, 'scroll', e => {
        if ((this.scrollParent == window && document.documentElement.scrollTop < this.scrollTop) || (this.scrollParent != window && this.scrollParent.scrollTop < this.scrollTop)) {
          this.element.classList.remove(this.scrollClass);
        } else  {
          this.element.classList.add(this.scrollClass);
        }
      })
      return this
    }
    console.log(this)
    IntersectionManager.addById('scr-sns-' + this.id, this.observedElement, (entry, observer) => {
      this.observerCallback(entry, observer)
    }, {
        root: this.observer.root,
        rootMargin: this.observer.rootMargin,
        threshold: this.observer.threshold
    })
    return this
  }

  destroy() {
    this.listeners.destroy()
    IntersectionManager.remove('responsive-navigation')
  }
}