import { getElement } from '~/General/Utility'
import Listeners from "~/Classes/Listeners"
import { IntersectionManager } from "~/Classes/ObserverManager.js"
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior"

export default class ScrollSensitive extends AbstractBehavior {
  static props = {
    scrollClass: '--scroll',
    classes: {},
    observer: {},
    cloneElementToRetainFlow: false,
    cloneClass: '-scroll-sense-clone',
    scrollTop: 0,
    scrollParent: window,
  }
  
  observerCallback(entry, observer) {
    for (let side of ['top', 'bottom']) {
      if (entry.boundingClientRect[side] > entry.rootBounds.top) {
        this.el.classList.remove(this.classes[side+'AboveView'])
        if (entry.boundingClientRect[side] < entry.rootBounds.bottom) {
          this.el.classList.add(this.classes[side+'InsideView'])
          this.el.classList.remove(this.classes[side+'BelowView'])
        } else {
          this.el.classList.add(this.classes[side+'BelowView'])
          this.el.classList.remove(this.classes[side+'InsideView'])
        }
      } else {
        this.el.classList.add(this.classes[side+'AboveView'])
        this.el.classList.remove(this.classes[side+'InsideView'])
        this.el.classList.remove(this.classes[side+'BelowView'])
      }
    }
  }

  mount() {
    this.observer = {
      root: null,
      rootMargin: '0px 0px 0px 0px',
      threshold: [0,1],
      target : null,
      ...this.observer
    }
    this.classes = {
      topInsideView: '--top-inside-view',
      topAboveView: '--top-above-view',
      topBelowView: '--top-below-view',
      bottomInsideView: '--bottom-inside-view',
      bottomAboveView: '--bottom-above-view',
      bottomBelowView: '--bottom-below-view',
      ...this.classes
    }
    this.observedElement = this.observer.target ? getElement(this.observer.target, 'ScrollSensitive') : this.el
    if (this.cloneElementToRetainFlow) {
      const clone = this.node.cloneNode(true)
      clone.classList.add(this.cloneClass)
      this.el.parentNode.insertBefore(clone, this.el)
      this.el = clone
    }
    this.listeners = new Listeners()
    if (this.scrollTop) {
      this.listeners.add(this.scrollParent, 'scroll', e => {
        if ((this.scrollParent == window && document.documentElement.scrollTop < this.scrollTop) || (this.scrollParent != window && this.scrollParent.scrollTop < this.scrollTop)) {
          this.el.classList.remove(this.scrollClass);
        } else  {
          this.el.classList.add(this.scrollClass);
        }
      })
      return this
    }
    IntersectionManager.addById(
        'scroll-sensitive-' + this.el.id,
        this.observedElement,
        (entry, observer) => { this.observerCallback(entry, observer) },
        { root: this.observer.root, rootMargin: this.observer.rootMargin, threshold: this.observer.threshold }
    )
    return this
  }

  destroy() {
    this.listeners.destroy()
    this.el.classList.remove(this.scrollClass)
    IntersectionManager.remove('scroll-sensitive-' + this.el.id)
  }
}