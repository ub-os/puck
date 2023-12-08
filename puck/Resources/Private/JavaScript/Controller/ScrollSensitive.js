import { $, $$, jsx, $target } from '~/Utility/DomUtility'
import Listeners from "~/Service/Listeners"
import { IntersectionManager } from "~/Service/ObserverCollector"
import Controller from "~/Application/Controller.js";


export default class ScrollSensitive extends Controller {
  static props = {
    classes: {
      topInsideView: '--top-inside-view',
      topAboveView: '--top-above-view',
      topBelowView: '--top-below-view',
      bottomInsideView: '--bottom-inside-view',
      bottomAboveView: '--bottom-above-view',
      bottomBelowView: '--bottom-below-view',
      scroll: '--scroll',
    },
    observer: {
      root: null,
      rootMargin: '0px 0px 0px 0px',
      threshold: [0,1],
      target : null,
    },
    cloneElementToRetainFlow: false,
    cloneClass: '-scroll-sense-clone',
    scrollTop: 0,
    scrollParent: null,
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

  connect() {
    this.observer = {
      ...this.constructor.props.observer,
      ...this.observer
    }
    this.classes = {
      ...this.constructor.props.classes,
      ...this.classes
    }
    this.observedElement = this.observer.target ? $target(this.observer.target, 'ScrollSensitive') : this.el
    if (this.cloneElementToRetainFlow) {
      const clone = this.node.cloneNode(true)
      clone.classList.add(this.cloneClass)
      this.el.parentNode.insertBefore(clone, this.el)
      this.el = clone
    }
    this.listeners = new Listeners()

    if (this.scrollTop) {
      this.listeners.add(this.scrollParent || window, 'scroll', e => {
        if ((this.scrollParent || document.documentElement).scrollTop < this.scrollTop) {
          this.el.classList.remove(this.classes.scroll);
        } else  {
          this.el.classList.add(this.classes.scroll);
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

  disconnect() {
    this.listeners.destroy()
    this.el.classList.remove(this.classes.scroll)
    IntersectionManager.remove('scroll-sensitive-' + this.el.id)
  }
}