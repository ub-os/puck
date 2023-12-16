import { $, $$, jsx, $target } from '~/Utility/DomUtility'
import ListenerCollector from "~/Service/ListenerCollector.js"
import { IntersectionManager } from "~/Service/ObserverCollector"
import Controller from "~/Application/Controller.js";


export default class ScrollSensitive extends Controller {
  static props = {
    topInsideClass: '--top-inside-view',
    topAboveClass: '--top-above-view',
    topBelowClass: '--top-below-view',
    bottomInsideClass: '--bottom-inside-view',
    bottomAboveClass: '--bottom-above-view',
    bottomBelowClass: '--bottom-below-view',
    scrollClass: '--scroll',
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
        this.el.classList.remove(this[side+'AboveClass'])
        if (entry.boundingClientRect[side] < entry.rootBounds.bottom) {
          this.el.classList.add(this[side+'InsideClass'])
          this.el.classList.remove(this[side+'BelowClass'])
        } else {
          this.el.classList.add(this[side+'BelowClass'])
          this.el.classList.remove(this[side+'InsideClass'])
        }
      } else {
        this.el.classList.add(this[side+'AboveClass'])
        this.el.classList.remove(this[side+'InsideClass'])
        this.el.classList.remove(this[side+'BelowClass'])
      }
    }
  }

  connect() {
    this.observer = {
      ...this.constructor.props.observer,
      ...this.observer
    }
    this.observedElement = this.observer.target ? $target(this.observer.target) : this.el
    if (this.cloneElementToRetainFlow) {
      const clone = this.node.cloneNode(true)
      clone.classList.add(this.cloneClass)
      this.el.parentNode.insertBefore(clone, this.el)
      this.el = clone
    }
    if (this.scrollTop) {
      this.listeners.add(this.scrollParent || window, 'scroll', e => {
        if ((this.scrollParent || document.documentElement).scrollTop < this.scrollTop) {
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

  disconnect() {
    this.listeners.destroy()
    this.el.classList.remove(this.scrollClass)
    IntersectionManager.remove('scroll-sensitive-' + this.el.id)
  }
}