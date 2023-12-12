import { $, $$, $id, $target } from '~/Utility/DomUtility'
import Listeners from "~/Service/Listeners";
import Controller from "~/Application/Controller.js";

export default class Toggleable extends Controller {
  static displayName = 'Toggleable'
  static events = {
    toggle: new Event('toggle'),
    toggleOn: new Event('toggleOn'),
    toggleOff: new Event('toggleOff'),
    groupToggle: new Event('groupToggle'),
  }
  static props = {
    active: false,
    groupId: '',
    duration: 0,
    trigger: 'click',
    classes: {
      active: '--active',
      inactive: '--inactive',
      activating: '--activating',
      deactivating: '--deactivating',
    },
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
    (this.toggles || []).forEach(t => t.classList[operation](className))
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
    this.active = true
    this.setClass('add', this.classes.active)
    if (transition) this.transitionClass(this.classes.activating)
    if (this.groupEl) {
      Toggleable.events.groupToggle.activeId = this.el.id
      this.groupEl.dispatchEvent(Toggleable.events.groupToggle)
    }
  }
  toggleOff(transition= true, changeUrlHash = true) {
    this.active = false
    this.setClass('remove', this.classes.active)
    if (transition) this.transitionClass(this.classes.deactivating)
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

  connect() {
    this.classes = {
      ...this.constructor.props.classes,
      ...this.classes
    }
    this.groupEl = this.groupId ? $id(this.groupId) : null
    this.toggles = $$(`[aria-controls="${this.el.id}"], [data-toggle-for="${this.el.id}"]`)
    if (this.hashToggles) {
      this.toggles = [
        ...this.toggles,
        ...$$(`a[href="/#${this.el.id}"], a[href="${window.location.pathname}#${this.el.id}"], a[href="${window.location.origin+window.location.pathname}#${this.el.id}"]`)
      ]
    }
    if (!this.toggles.length) {
      console.warn(`Toggleable: No toggles found for ${this.el.id}`)
      console.trace()
    }
    if (this.pauseMediaOnOff) {
      this.mediaChildren = this.el.$$('video, audio')
    }
    if (this.reloadIframeOnOff) {
      this.iframeChildren = this.el.$$('iframe')
    }
    this.listeners = new Listeners()
    this.active ? this.toggleOn(false) : this.toggleOff(false, false)
    this.toggles.forEach(t => {
      //if (!t.getAttribute('aria-controls')) t.setAttribute('aria-controls', this.el.id)
      //t.hash = ''
      switch (this.trigger) {
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
            e.preventDefault()
            if (t.getAttribute('href') === `/#${this.el.id}`) {
              this.dispatchToggle(t, Toggleable.events.toggleOn)
            } else {this.dispatchToggle(t)}
          })
      }
    })
    this.listeners.add(this.el, 'toggle', () => {
      if (this.durationTimer === null) {
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
    if (this.outClickOff) {
      const target = this.outTarget ? $target(this.outTarget, 'Toggleable') : this.el
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
    if (this.scrollOff) {
      this.listeners.add(window, 'scroll', () => {
        if (this.active) this.el.dispatchEvent(Toggleable.events.toggleOff)
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
  disconnect() {
    this.toggleOff(false, false)
    if (this.listeners) {
      this.listeners.destroy()
    }
  }
}
