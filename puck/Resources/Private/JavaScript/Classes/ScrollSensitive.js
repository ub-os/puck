import {getNode} from '../General/Functions'

export default class ScrollSensitive {
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
        entries => {
          entries.forEach(e => {
            if (e.boundingClientRect.top > 0) {
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
    observerOptions,
    observationTarget,
    cloneElementToRetainFlow = false,
    cloneClass = '-scroll-sense-clone',
    scrollTop = 0,
    scrollParent = window,
    ...options})
  {
    Object.assign(this, { scrollClass, scrollTop, observerOptions, cloneElementToRetainFlow, scrollParent, ...options })
    this.node = getNode(target, 'ScrollSensitive')
    this.observedNode = observationTarget ? getNode(observationTarget, 'ScrollSensitive') : this.node
    if (cloneElementToRetainFlow) {
      const clone = this.node.cloneNode(true)
      clone.classList.add(cloneClass)
      this.node.parentNode.insertBefore(clone, this.node)
      this.node = clone
    }
  }
  mount() {
    if (this.scrollTop) {
      this.scrollParent.addEventListener('scroll', e => {
        if ((this.scrollParent == window && document.documentElement.scrollTop < this.scrollTop) || (this.scrollParent != window && this.scrollParent.scrollTop < this.scrollTop)) {
          this.node.classList.remove(this.scrollClass);
        } else  {
          this.node.classList.add(this.scrollClass);
        }
      })
      return this
    }
    if (this.observerOptions) {
      this.constructor.createIntersectionObserver(this.observerOptions).observe(this.observedNode)
    } else {
      this.constructor.defaultObserver.observe(this.observedNode)
    }
    this.observedNode.addEventListener('addScrollClass', () => {
      this.node.classList.add(this.scrollClass)
    })
    this.observedNode.addEventListener('removeScrollClass', () => {
      this.node.classList.remove(this.scrollClass)
    })
    return this
  }
}