import { getElement } from '../General/Functions'
import AbstractComponent from "./AbstractComponent";

const scrollSensitiveObserverMap = new Map()

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

  createIntersectionObserver(
      options,
      classes,
      classTarget,
  ) {
    const observer =  new IntersectionObserver(
        (entries, observer) => {
          entries.forEach(e => {
            for (let side of ['top', 'bottom']) {
              if (e.boundingClientRect[side] > e.rootBounds.top) {
                classTarget.classList.remove(classes[side+'AboveView'])
                if (e.boundingClientRect[side] < e.rootBounds.bottom) {
                  classTarget.classList.add(classes[side+'InsideView'])
                  classTarget.classList.remove(classes[side+'BelowView'])
                } else {
                  classTarget.classList.add(classes[side+'BelowView'])
                  classTarget.classList.remove(classes[side+'InsideView'])
                }
              } else {
                classTarget.classList.add(classes[side+'AboveView'])
                classTarget.classList.remove(classes[side+'InsideView'])
                classTarget.classList.remove(classes[side+'BelowView'])
              }
            }
          })
        },
        options)
    return observer
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

    // stringify the observer options to create a unique key for the map
    const optionsString = JSON.stringify({
      observer: this.observer,
      classes: this.classes
    })
    if (!scrollSensitiveObserverMap.get(optionsString)) {
      scrollSensitiveObserverMap.set(
          optionsString,
          this.createIntersectionObserver(
              this.observer,
              this.classes,
              this.element)
      )
    }
    scrollSensitiveObserverMap.get(optionsString).observe(this.observedElement)
    return this
  }
}