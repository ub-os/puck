import { getElement } from '../../General/Utility'
import Listeners from "../Listeners";
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior";

export default class Toggleable extends AbstractBehavior {
  static displayName = 'Toggleable'
  static events = {
    toggle: new Event('toggle'),
    toggleOn: new Event('toggleOn'),
    toggleOff: new Event('toggleOff'),
    groupToggle: new Event('groupToggle'),
  }
  static props = {
    active: false,
    alwaysActive: false,
    groupId: '',
    clickDelay: 0,
    triggerOn: 'click',
    stateClasses: {},
    // only one toggleable in group can be active
    exclusiveGroup: true,
    // disable toggles
    disableToggles: false,
    // add links with matching url hash to toggles
    hashLinkToggles: true,
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
    outsideClickOff: false,
    // target which is checked for outside click
    outsideTarget: null,
    // media is paused on toggle off
    pauseMediaOnOff: true,
    // iframes are reloaded on toggle off
    reloadIframeOnOff: true,
    // toggle off only works on the toggle that toggled on
    switchToggles: false,
  }
  setClass(operation, className) {
    this.el.classList[operation](className)
    if (this.parentClassing) {
      this.el.parentNode.classList[operation](className)
    }
    if (this.documentClassing) {
      document.documentElement.classList[operation](`--${this.el.id}-${this.constructor.displayName.toLowerCase()}${className}`)
    }
    (this.toggles || []).forEach(t => t.classList[operation](className))
  }
  transitionClass(className) {
    if (this.clickDelay > 0) {
      this.setClass('add', className)
      this.clickDelayTimer = setTimeout(() => {
        this.setClass('remove', className)
        clearTimeout(this.clickDelayTimer)
        this.clickDelayTimer = null
      }, this.clickDelay)
    }
  }
  toggleOn(transition= true) {
    this.active = true
    this.setClass('add', this.stateClasses.active)
    if (transition) this.transitionClass(this.stateClasses.activating)
    if (this.groupEl) {
      Toggleable.events.groupToggle.activeId = this.el.id
      this.groupEl.dispatchEvent(Toggleable.events.groupToggle)
    }
  }
  toggleOff(transition= true, changeUrlHash = true) {
    this.active = false
    this.setClass('remove', this.stateClasses.active)
    if (transition) this.transitionClass(this.stateClasses.deactivating)
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
      history.replaceState("", document.title, location.href.replace(`#${this.el.id}`, '')) // remove hash from url
    }
  }
  toggle() {
    if (this.active && (!this.switchToggles || this.switchToggles && this.currentToggle === this.lastUsedToggle)) {
      this.el.dispatchEvent(Toggleable.events.toggleOff)
    } else {
      this.el.dispatchEvent(Toggleable.events.toggleOn)
    }
  }
  dispatchToggle(toggle = null, event = Toggleable.events.toggle) {
    if (toggle) this.currentToggle = toggle
    this.el.dispatchEvent(event)
    if (toggle) this.lastUsedToggle = toggle
  }
  mount() {
    super.mount()
    this.stateClasses = {
      ...{
        active: '--active',
        inactive: '--inactive',
        activating: '--activating',
        deactivating: '--deactivating',
      },
      ...this.stateClasses
    }
    this.lastUsedToggle = null
    this.clickDelayTimer = null
    this.groupEl = this.groupId ? document.getElementById(this.groupId) : null
    this.toggles = document.querySelectorAll(`[aria-controls="${this.el.id}"]`)
    if (this.hashLinkToggles) {
      this.toggles = [
        ...this.toggles,
        ...document.querySelectorAll(`a[href="/#${this.el.id}"], a[href="${window.location.pathname}#${this.el.id}"], a[href="${window.location.origin+window.location.pathname}#${this.el.id}"]`)
      ]
    }
    if (!this.toggles.length) {
      console.warn(`Toggleable: No toggles found for ${this.el.id}`)
      console.trace()
    }
    if (this.pauseMediaOnOff) {
      this.mediaChildren = this.el.querySelectorAll('video, audio')
    }
    if (this.reloadIframeOnOff) {
      this.iframeChildren = this.el.querySelectorAll('iframe')
    }
    this.listeners = new Listeners()
    this.active ? this.toggleOn(false) : this.toggleOff(false, false)
    this.toggles.forEach(t => {
      t.setAttribute('aria-controls', this.el.id)
      switch (this.triggerOn) {
        case 'hover':
          this.listeners.add(t, 'mouseenter', () => {
            if (this.disableToggles) return
            this.dispatchToggle(t, Toggleable.events.toggleOn)
          })
          this.listeners.add(t, 'mouseleave', () => {
            if (this.disableToggles) return
            this.dispatchToggle(t, Toggleable.events.toggleOff)
          })
          break
        default:
          this.listeners.add(t, 'click', e => {
            if (this.disableToggles) return
            if (e.target.closest('[data-toggle-stop]')) return
            if (t.getAttribute('href') === `/#${this.el.id}`) {
              this.dispatchToggle(t, Toggleable.events.toggleOn)
            } else {this.dispatchToggle(t)}
          })
      }
    })
    this.listeners.add(this.el, 'toggle', () => {
      if (this.clickDelayTimer === null) {
        this.toggle()
      }
    })
    this.listeners.add(this.el, 'toggleOn', () => {
      this.toggleOn()
    })
    this.listeners.add(this.el, 'toggleOff', () => {
      this.toggleOff()
    })
    if (this.groupEl) {
      this.listeners.add(this.groupEl, 'groupToggle', event => {
        if (this.exclusiveGroup && this.active && !this.alwaysActive && event.activeId !== this.el.id) {
          this.el.dispatchEvent(Toggleable.events.toggleOff)
        }
      })
    }
    if (this.outsideClickOff) {
      const target = this.outsideTarget ? getElement(this.outsideTarget, 'Toggleable') : this.el
      this.listeners.add(document, 'click', event => {
        if (this.active) {
          let targetInToggles = false
          this.toggles.forEach(t => {
            if (t.contains(event.target)) targetInToggles = true
          })
          if (!target.contains(event.target) && !targetInToggles) this.el.dispatchEvent(Toggleable.events.toggleOff)
        }
      })
    }
    if (this.escOff) {
      this.listeners.add(this.el, 'keydown', event => {
        if (event.key === 'Escape') this.el.dispatchEvent(Toggleable.events.toggleOff)
      })
    }
    if (this.urlHashOn) {
      if (window.location.hash.split('?')[0] === `#${this.el.id}`) this.el.dispatchEvent(Toggleable.events.toggleOn)
      this.listeners.add(window, 'hashchange', event => {
        if (window.location.hash.split('?')[0] === `#${this.el.id}`) this.el.dispatchEvent(Toggleable.events.toggleOn)
      })
    }
    return this
  }
  destroy() {
    if (this.listeners) {
      this.listeners.destroy()
    }
  }
}
