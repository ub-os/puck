import { $, $$, $id, $target } from '~/_jcores/Utility/DomUtility'
import Core from "~/_jcores/Core"

/**
 * @property {Map} toggleUnits
 */
export default class Toggleable extends Core {
  static displayName = 'Toggleable'
  static events = {
    toggleOn: 'toggle-on',
    toggleOff: 'toggle-off',
    toggleGroup: 'toggle-group',
  }
  static units = ['toggle']
  static attributes = {
    active: false,
    groupId: '',
    duration: 0,
    trigger: 'click',
    activeClass: '--active',
    inactiveClass: '--inactive',
    activatingClass: '--activating',
    deactivatingClass: '--deactivating',
    // only one toggleable in group can be active
    exclusiveGroup: true,
    // disable toggles
    disableToggles: false,
    // add links with matching url hash to toggles
    hashToggles: true,
    // set state classes on parent
    parentClassing: false,
    // set state classes on documentElement
    documentClassing: true,
    // toggle on if url hash matches
    urlHashOn: true,
    // remove matching hash from url on toggle off
    urlHashRemove: true,
    // toggle off on esc
    escOff: true,
    // toggle off on outside click
    outClickOff: false,
    // toggle off on self click
    selfClickOff: false,
    // toggle off on scroll
    scrollOff: false,
    // target which is checked for outside click
    outTarget: null,
    // media is paused on toggle off
    pauseMediaOnOff: true,
    // iframes are reloaded on toggle off
    reloadIframeOnOff: true,
    // toggle off only works on the toggle that toggled on
    switchToggles: false,
    alwaysActive: false,
  }
  static triggerables = {
    toggle: {
      transition: true
    }
  }

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
    this.toggleUnits.forEach(t => t.classList[operation](className))
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
  toggleOn(transition= true) {
    //if (this.active) return
    this.active = true
    this.setClass('add', this.activeClass)
    if (transition) this.transitionClass(this.activatingClass)
    if (this.groupEl) {
      this.groupEl.dispatchEvent(
          new CustomEvent(
              Toggleable.events.toggleGroup,
              { detail: {activeId: this.el.id} }
          ))
    }
  }
  toggleOff(transition= true, changeUrlHash = true) {
    //if (!this.active) return
    this.active = false
    this.setClass('remove', this.activeClass)
    if (transition) this.transitionClass(this.deactivatingClass)
    if (this.pauseMediaOnOff && this.mediaChildren) {
      this.mediaChildren.forEach(item => {
        if (item.pause) item.pause()
      })
    }
    if (this.reloadIframeOnOff && this.iframeChildren) {
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
  }

  toggle(event, { transition } = {}) {
    if (this.active && (!this.switchToggles || (this.switchToggles && event.currentTarget === this.lastUsedToggle))) {
      this.dispatch(Toggleable.events.toggleOff)
    } else {
      this.dispatch(Toggleable.events.toggleOn)
    }
    this.lastUsedToggle = event.currentTarget
  }

  toggleUnitConnected(el) {
    el.ariaControls = this.el.id
    if (this.active) {
      el.classList.add(this.activeClass)
    }
  }

  connect() {
    this.groupEl = this.groupId ? $id(this.groupId) : null
    if (this.pauseMediaOnOff) {
      this.mediaChildren = this.el.$$('video, audio')
    }
    if (this.reloadIframeOnOff) {
      this.iframeChildren = this.el.$$('iframe')
    }
    this.active ? this.toggleOn(false) : this.toggleOff(false, false)
    this.listeners.add(this.el, Toggleable.events.toggleOn, e => {
      if (e.defaultPrevented) return
      window.requestAnimationFrame(() => {
        this.toggleOn()
      })
    })
    this.listeners.add(this.el, Toggleable.events.toggleOff, e => {
      if (e.defaultPrevented) return
      this.toggleOff()
    })
    this.listeners.add(document.body, 'toggle-off-all', e => {
      if (e.defaultPrevented) return
      this.toggleOff(e.detail?.transition)
    })
    if (this.groupEl) {
      this.listeners.add(this.groupEl, Toggleable.events.toggleGroup, e => {
        if (e.defaultPrevented) return
        if (this.exclusiveGroup && this.active && !this.alwaysActive && e.detail.activeId !== this.el.id) {
          this.dispatch(Toggleable.events.toggleOff)
        }
      })
    }
    if (this.outClickOff) {
      const target = this.outTarget ? $target(this.outTarget, 'Toggleable') : this.el
      this.listeners.add(document, 'click', event => {
        if (!this.active || target.contains(event.target)) return
        this.dispatch(Toggleable.events.toggleOff)
      })
    }
    if (this.selfClickOff) {
      this.listeners.add(this.el, 'click', event => {
        if (this.active && event.target === this.el) this.dispatch(Toggleable.events.toggleOff)
      })
    }
    if (this.escOff) {
      this.listeners.add(this.el, 'keydown', event => {
        if (event.key === 'Escape') this.dispatch(Toggleable.events.toggleOff)
      })
    }
    if (this.scrollOff) {
      this.listeners.add(window, 'scroll', () => {
        if (this.active) this.dispatch(Toggleable.events.toggleOff)
      }, {passive: true})
    }
    if (this.urlHashOn) {
      if (window.location.hash.split('?')[0] === `#${this.el.id}`) this.dispatch(Toggleable.events.toggleOn)
      this.listeners.add(this.el, 'hash-link-click', event => {
        this.lastUsedToggle = event.detail.linkElement
        this.dispatch(Toggleable.events.toggleOn)
      })
    }
    return this
  }
  disconnect() {
    this.toggleOff(false, false)
    if (this.listeners) {
      this.listeners.destroy()
    }
  }
}
