import { $, $$, jsx, $target } from '~/Utility/DomUtility'
import ListenerCollector from "~/Service/ListenerCollector.js"
import { IntersectionManager, ResizeManager } from "~/Service/ObserverCollector"
import Controller from "~/Application/Controller.js";


export default class ScrollSensitive extends Controller {
  static attributes = {
    topInsideClass: '--top-inside-view',
    topAboveClass: '--top-above-view',
    topBelowClass: '--top-below-view',
    bottomInsideClass: '--bottom-inside-view',
    bottomAboveClass: '--bottom-above-view',
    bottomBelowClass: '--bottom-below-view',
    scrollClass: '--scroll',
    root: null,
    rootMargin: '0px 0px 0px 0px',
    threshold: [0,1],
    target : null,
    cloneElementToRetainFlow: false,
    cloneClass: '-scroll-sense-clone',
    scrollTop: ''
  }
  
  observerCallback(entry, observer) {
    for (let side of ['top', 'bottom']) {
      if (entry.boundingClientRect[side] >= entry.rootBounds.top) {
        this.el.classList.remove(this[side+'AboveClass'])
        if (entry.boundingClientRect[side] <= entry.rootBounds.bottom) {
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

  __correctScrollTop = null

  get correctScrollTop() {
    if (this.__correctScrollTop !== null) return this.__correctScrollTop
    let scrollTop = 0
    for (let topBreakpoint of this.scrollTop.split(' ')) {
      const [ top, breakpoint ] = topBreakpoint.split('@')
      if (!breakpoint || window.innerWidth > parseInt(breakpoint)) {
        scrollTop = parseInt(top)
      }
    }
    return this.__correctScrollTop = scrollTop
  }

  scrollTopChanged() {
    this.__correctScrollTop = null
  }

  connect() {
    this.root = this.root ? $(this.root) : null
    if (this.cloneElementToRetainFlow) {
      const clone = this.node.cloneNode(true)
      clone.classList.add(this.cloneClass)
      this.el.parentNode.insertBefore(clone, this.el)
      this.el = clone
    }
    if (this.scrollTop) {
      this.listeners.add(this.root || window, 'scroll', e => {
        if ((this.root || document.documentElement).scrollTop < this.correctScrollTop) {
          this.el.classList.remove(this.scrollClass);
        } else {
          this.el.classList.add(this.scrollClass);
        }
      })
      ResizeManager.addById(`scroll-sensitive-resize-${this.el.id}`, document.body, () => {
        this.__correctScrollTop = null
      })
      return this
    }
    IntersectionManager.addById(
        'scroll-sensitive-' + this.el.id,
        this.target ? $target(this.target) : this.el,
        (entry, observer) => { this.observerCallback(entry, observer) },
        { root: this.root, rootMargin: this.rootMargin, threshold: this.threshold }
    )
    return this
  }

  disconnect() {
    this.listeners.destroy()
    this.el.classList.remove(this.scrollClass)
    IntersectionManager.remove('scroll-sensitive-' + this.el.id)
    ResizeManager.remove(`scroll-sensitive-resize-${this.el.id}`)
  }
}