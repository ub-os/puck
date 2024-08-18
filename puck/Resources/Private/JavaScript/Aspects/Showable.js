import { $, $$, $id, $target } from '~/Utility/DomUtility'
import { ElementAspect } from "~/_jcores"
import EventHandlerSet from "~/Helper/EventHandlerSet"

/**
 * @property {Map} toggleElements
 */
export default class Showable extends ElementAspect {
  static displayName = 'Showable'
  static events = {
    show: 'showable:show',
    hide: 'showable:hide',
    toggleGroup: 'showable:toggle-group',
  }
  static connectedElements = ['toggle']
  static attributes = {
    active: false,
    groupId: '',
    duration: 0,
    trigger: 'click',
    activeClass: '--active',
    inactiveClass: '--inactive',
    activatingClass: '--activating',
    deactivatingClass: '--deactivating',
    // only one showable in group can be active
    exclusiveGroup: true,
    // disable toggles
    disableToggles: false,
    // add links with matching url hash to toggles
    hashToggles: true,
    // set state classes on parent
    parentClassing: false,
    // set state classes on documentElement
    documentClassing: true,
    // show if url hash matches
    urlHashShow: true,
    // remove matching hash from url on hide
    urlHashRemove: true,
    // hide on esc
    escHide: true,
    // hide on outside click
    outClickHide: false,
    // hide on self click
    selfClickHide: false,
    // hide on scroll
    scrollHide: false,
    // hide if matching element is clicked
    clickHideSelector: '',
    // target which is checked for outside click
    outTarget: null,
    // media is paused on hide
    pauseMediaOnHide: true,
    // iframes are reloaded on hide
    reloadIframeOnHide: true,
    // hide only works on the toggle that toggled on
    switchToggles: false,
    alwaysActive: false,
  }

  handlerSet = new EventHandlerSet()
  lastUsedToggle = null
  durationTimer = null

  setClass(operation, className) {
    this.el.classList[operation](className)
    if (this.parentClassing) {
      this.el.parentNode.classList[operation](className)
    }
    if (this.documentClassing) {
      document.documentElement.classList[operation](`--${this.el.id}-${this.constructor.displayName.toLowerCase()}${className}`)
    }
    this.toggleElements.forEach(t => t.classList[operation](className))
  }
  transitionClass(className) {
    if (this.duration > 0) {
      this.setClass('add', className)
      this.durationTimer = setTimeout(() => {
        this.setClass('remove', className)
        this.durationTimer = null
      }, this.duration)
    }
  }
  show({ transition = true } = {}) {
    //if (this.active) return
    this.active = true
    this.setClass('add', this.activeClass)
    if (transition) this.transitionClass(this.activatingClass)
    this.dispatch(Showable.events.show)
    if (this.groupEl) {
      this.groupEl.dispatchEvent(
          new CustomEvent(
              Showable.events.toggleGroup,
              { detail: {activeId: this.el.id} }
          ))
    }
  }
  hide({ transition = true, changeUrlHash = true } = {}) {
    //if (!this.active) return
    this.active = false
    this.setClass('remove', this.activeClass)
    if (transition) this.transitionClass(this.deactivatingClass)
    if (this.pauseMediaOnHide && this.mediaChildren) {
      this.mediaChildren.forEach(item => {
        if (item.pause) item.pause()
      })
    }
    if (this.reloadIframeOnHide && this.iframeChildren) {
      this.iframeChildren.forEach(item => {
        if (item.src) {
          let src = item.src
          item.src = src
        }
      })
    }
    if (changeUrlHash && this.urlHashRemove && window.location.hash.split('?')[0] === `#${this.el.id}`) {
      history.replaceState(history.state, document.title, location.href.replace(`#${this.el.id}`, '')) // remove hash from url
    }
    this.dispatch(Showable.events.hide)
  }

  toggle({ transition = true, changeUrlHash = true } = {}, event = {}) {
    if (this.active && (!this.switchToggles || (this.switchToggles && event.handlerTarget === this.lastUsedToggle))) {
      this.hide({ transition, changeUrlHash })
    } else {
      this.show({ transition })
    }
    this.lastUsedToggle = event.handlerTarget
  }

  toggleElementConnected(el) {
    el.ariaControls = this.el.id
    if (this.active) {
      el.classList.add(this.activeClass)
    }
  }

  connect() {
    this.groupEl = this.groupId ? $id(this.groupId) : null
    if (this.pauseMediaOnHide) {
      this.mediaChildren = this.el.$$('video, audio')
    }
    if (this.reloadIframeOnHide) {
      this.iframeChildren = this.el.$$('iframe')
    }
    this.active ? this.show({ transition: false }) : this.hide({ transition: false, changeUrlHash: false })

    if (this.groupEl) {
      this.handlerSet.add(this.groupEl, Showable.events.toggleGroup, e => {
        if (e.defaultPrevented) return
        if (this.exclusiveGroup && this.active && !this.alwaysActive && e.detail.activeId !== this.el.id) {
          this.hide()
        }
      })
    }
    if (this.outClickHide) {
      const target = this.outTarget ? $target(this.outTarget, 'Showable') : this.el
      this.handlerSet.add(document, 'click', event => {
        if (this.active && !target.contains(event.target)) this.hide()
      })
    }
    if (this.selfClickHide) {
      this.handlerSet.add(this.el, 'click', event => {
        if (this.active && event.target === this.el) this.hide()
      })
    }
    if (this.escHide) {
      this.handlerSet.add(this.el, 'keydown', event => {
        if (event.key === 'Escape') this.hide()
      })
    }
    if (this.scrollHide) {
      this.handlerSet.add(window, 'scroll', () => {
        if (this.active) this.hide()
      }, {passive: true})
    }
    if (this.clickHideSelector) {
      this.handlerSet.addDelegate(this.el, this.clickHideSelector, 'click', event => {
        if (this.active) this.hide()
      })
    }
    if (this.urlHashShow) {
      if (window.location.hash.split('?')[0] === `#${this.el.id}`) this.show()
      this.handlerSet.add(this.el, 'hash-link-click', event => {
        this.lastUsedToggle = event.detail.linkElement
        this.show()
      })
    }
    return this
  }
  disconnect() {
    this.hide({ transition: false, changeUrlHash: false })
    this.handlerSet.clear()
  }
}
