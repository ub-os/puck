import { $, $$, $id, $target } from '~/Utility/DomUtility'
import ListenerCollector from "~/Service/ListenerCollector.js";
import Controller from "~/Application/Controller.js";

export default class Toggleable extends Controller {
  static displayName = 'Toggleable'
  static events = {
    toggle: new Event('toggle'),
    toggleOn: new Event('toggle-on'),
    toggleOff: new Event('toggle-off'),
    groupToggle: new Event('toggle-group'),
  }
  static targets = ['toggle']
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
    this.toggleTargets.forEach(t => t.classList[operation](className))
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
    if (this.active) return
    this.active = true
    this.setClass('add', this.activeClass)
    if (transition) this.transitionClass(this.activatingClass)
    if (this.groupEl) {
      Toggleable.events.groupToggle.activeId = this.el.id
      this.groupEl.dispatchEvent(Toggleable.events.groupToggle)
    }
  }
  toggleOff(transition= true, changeUrlHash = true) {
    if (!this.active) return
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
      this.el.dispatchEvent(Toggleable.events.toggleOff)
    } else {
      this.el.dispatchEvent(Toggleable.events.toggleOn)
    }
    this.lastUsedToggle = event.currentTarget
  }

  toggleConnected(el) {
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
    this.listeners.add(this.el, 'toggle', event => {
      if (this.durationTimer === null) {
        this.toggle(event)
      }
    })
    this.listeners.add(this.el, 'toggle-on', e => {
      if (e.defaultPrevented) return
      this.toggleOn()
    })
    this.listeners.add(this.el, 'toggle-off', e => {
      if (e.defaultPrevented) return
      this.toggleOff()
    })
    this.listeners.add(document.body, 'toggle-off-all', e => {
      if (e.defaultPrevented) return
      this.toggleOff(e.detail.transition)
    })
    if (this.groupEl) {
      this.listeners.add(this.groupEl, 'toggle-group', event => {
        if (this.exclusiveGroup && this.active && !this.alwaysActive && event.activeId !== this.el.id) {
          this.el.dispatchEvent(Toggleable.events.toggleOff)
        }
      })
    }
    if (this.outClickOff) {
      const target = this.outTarget ? $target(this.outTarget, 'Toggleable') : this.el
      this.listeners.add(document, 'click', event => {
        if (!this.active) return
        window.requestAnimationFrame(() => {
          if (target.contains(event.target) || event.actionTrigger?.controller?.el.id === this.el.id) return
          this.el.dispatchEvent(Toggleable.events.toggle)
        })
      })
    }
    if (this.escOff) {
      this.listeners.add(this.el, 'keydown', event => {
        if (event.key === 'Escape') this.el.dispatchEvent(Toggleable.events.toggleOff)
      })
    }
    if (this.scrollOff) {
      this.listeners.add(window, 'scroll', () => {
        if (this.active) this.el.dispatchEvent(Toggleable.events.toggleOff)
      })
    }
    if (this.urlHashOn) {
      if (window.location.hash.split('?')[0] === `#${this.el.id}`) this.el.dispatchEvent(Toggleable.events.toggleOn)
      this.listeners.add(this.el, 'hash-link-clicked', event => {
        this.lastUsedToggle = event.detail.linkElement
        this.el.dispatchEvent(Toggleable.events.toggleOn)
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
