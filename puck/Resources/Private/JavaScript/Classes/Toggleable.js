import {getElement} from '../General/Functions'
import AbstractComponent from "./AbstractComponent.js";

export default class Toggleable extends AbstractComponent{
  static events = {
    toggle: new Event('toggle'),
    toggleOn: new Event('toggleOn'),
    toggleOff: new Event('toggleOff'),
    groupToggle: new Event('groupToggle'),
  }
  constructor(target, {
    toggles,
    active = false,
    alwaysActive = false,
    groupId = null,
    exclusiveGroup = true,
    clickDelay = 0,
    triggerOn = 'click',
    classes = {},
    setClassOnParent = false,
    toggleOnIfUrlHashMatches = true,
    removeMatchingUrlHashOnToggleOff = true,
    toggleOffOnEsc = true,
    toggleOffOnOutsideClick = false,
    toggleOffOnOutsideClickTarget = null,
    disableToggles = false,
    addMatchingHashLinksToToggles = toggleOnIfUrlHashMatches,
    pauseMediaOnToggle = true,
    reloadIframeOnToggle = pauseMediaOnToggle,
    disableToggleOffIfToggleNotLastUsedToggle = false,
  })
  {
    super(target)
    Object.assign(this, {
      active, alwaysActive, groupId, exclusiveGroup, clickDelay, triggerOn, setClassOnParent,
      removeMatchingUrlHashOnToggleOff, toggleOffOnOutsideClick, toggleOffOnOutsideClickTarget,
      toggleOffOnEsc, toggleOnIfUrlHashMatches, addMatchingHashLinksToToggles, disableToggles,
      pauseMediaOnToggle, reloadIframeOnToggle, disableToggleOffIfToggleNotLastUsedToggle })

    this.classes = {
      ...{
        active: '--active',
        inactive: '--inactive',
        activating: '--activating',
        deactivating: '--deactivating',
      },
      ...classes
    }
    this.lastUsedToggle = null
    this.clickDelayTimer = null
    this.groupElement = groupId ? document.getElementById(groupId) : null
    this.toggles = toggles || document.querySelectorAll(`[aria-controls="${this.id}"]`)
    if (this.addMatchingHashLinksToToggles) {
      this.toggles = [
        ...this.toggles,
        ...document.querySelectorAll(`a[href="/#${this.id}"], a[href="${window.location.pathname}#${this.id}"], a[href="${window.location.origin+window.location.pathname}#${this.id}"]`)
      ]
    }
    if (!this.toggles.length) {
      console.warn(`Toggleable: No toggles found for ${this.id}`)
      console.trace()
    }
    if (this.pauseMediaOnToggle) {
      this.mediaContent = this.element.querySelectorAll('video, audio')
    }
  }
  setClass(operation, className) {
    this.element.classList[operation](className)
    if (this.setClassOnParent) {
      this.element.parentNode.classList[operation](className)
    }
    document.documentElement.classList[operation](`--${this.id}-${this.constructor.name.toLowerCase()}${className}`)
    this.toggles.forEach(t => t.classList[operation](className))
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
    this.setClass('add', this.classes.active)
    if (transition) this.transitionClass(this.classes.activating)
    if (this.groupElement) {
      Toggleable.events.groupToggle.activeId = this.id
      this.groupElement.dispatchEvent(Toggleable.events.groupToggle)
    }
  }
  toggleOff(transition= true, changeUrlHash = true) {
    this.active = false
    this.setClass('remove', this.classes.active)
    if (transition) this.transitionClass(this.classes.deactivating)
    if (this.pauseMediaOnToggle && this.mediaContent) {
      this.mediaContent.forEach(item => {
        if (item.pause) item.pause()
      })
    }
    if (this.reloadIframeOnToggle && this.element.querySelectorAll('iframe')) {
      this.element.querySelectorAll('iframe').forEach(item => {
        if (item.src) {
          let src = item.src
          item.src = src
        }
      })
    }
    if (changeUrlHash && this.removeMatchingUrlHashOnToggleOff && window.location.hash === `#${this.id}`) {
      history.replaceState("", document.title, window.location.pathname + window.location.search)
    }
  }
  toggle() {
    if (this.active && (!this.disableToggleOffIfToggleNotLastUsedToggle || this.disableToggleOffIfToggleNotLastUsedToggle && this.currentToggle === this.lastUsedToggle)) {
      this.element.dispatchEvent(Toggleable.events.toggleOff)
    } else {
      this.element.dispatchEvent(Toggleable.events.toggleOn)
    }
  }
  dispatchToggle(toggle = null, event = Toggleable.events.toggle) {
    if (toggle) this.currentToggle = toggle
    this.element.dispatchEvent(event)
    if (toggle) this.lastUsedToggle = toggle
  }
  mount() {
    this.active ? this.toggleOn(false) : this.toggleOff(false, false)
    this.toggles.forEach(t => {
      t.setAttribute('aria-controls', this.id)
      switch (this.triggerOn) {
        case 'hover':
          t.addEventListener('mouseenter', () => {
            if (this.disableToggles) return
            this.dispatchToggle(t, Toggleable.events.toggleOn)
          })
          t.addEventListener('mouseleave', () => {
            if (this.disableToggles) return
            this.dispatchToggle(t, Toggleable.events.toggleOff)
          })
          break
        default:
          t.addEventListener('click', (e) => {
            if (this.disableToggles) return
            if (e.target.closest('[data-toggle-stop]')) return
            if (t.getAttribute('href') === `/#${this.id}`) {
              this.dispatchToggle(t, Toggleable.events.toggleOn)
            } else {this.dispatchToggle(t)}
          })
      }
    })
    this.element.addEventListener('toggle',  event => {
      if (this.clickDelayTimer === null) {
        this.toggle()
      }
    })
    this.element.addEventListener('toggleOn',  event => {
      this.toggleOn()
    })
    this.element.addEventListener('toggleOff',  event => {
      this.toggleOff()
    })
    if (this.groupElement) {
      this.groupElement.addEventListener('groupToggle', event => {
        if (this.exclusiveGroup && this.active && !this.alwaysActive && event.activeId !== this.id) this.element.dispatchEvent(Toggleable.events.toggleOff)
      })
    }
    if (this.toggleOffOnOutsideClick) {
      const target = this.toggleOffOnOutsideClickTarget ? getElement(this.toggleOffOnOutsideClickTarget, 'Toggleable') : this.element
      document.addEventListener('click', event => {
        if (this.active) {
          let targetInToggles = false
          this.toggles.forEach(t => {
            if (t.contains(event.target)) targetInToggles = true
          })
          if (!target.contains(event.target) && !targetInToggles) this.element.dispatchEvent(Toggleable.events.toggleOff)
        }
      })
    }
    if (this.toggleOffOnEsc) {
      this.element.addEventListener('keydown', event => {
        if (event.key === 'Escape') this.element.dispatchEvent(Toggleable.events.toggleOff)
      })
    }
    if (this.toggleOnIfUrlHashMatches) {
      if (window.location.hash === `#${this.id}`) this.element.dispatchEvent(Toggleable.events.toggleOn)
      window.addEventListener('hashchange', event => {
        if (window.location.hash === `#${this.id}`) this.element.dispatchEvent(Toggleable.events.toggleOn)
      })
    }
    return this
  }

}