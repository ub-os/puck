import {getNode} from '../General/Functions'

const toggleEvents = {
    toggle: new Event('toggle'),
    toggleOn: new Event('toggleOn'),
    toggleOff: new Event('toggleOff'),
    groupToggle: new Event('groupToggle'),
}

class Toggleable {
  constructor(target, {
        toggles,
        active = false,
        alwaysActive = false,
        groupId = null,
        exclusiveGroup = true,
        clickDelay = 0,
        classes = {},
        toggleOnIfUrlHashMatches = true,
        toggleOffOnEsc = true,
        toggleOffOnOutsideClick = false,
        disableToggles = false,
        addMatchingHashLinksToToggles = toggleOnIfUrlHashMatches
      })
  {
    Object.assign(this, { active, alwaysActive, groupId, exclusiveGroup, clickDelay, toggleOffOnOutsideClick, toggleOffOnEsc, toggleOnIfUrlHashMatches, addMatchingHashLinksToToggles, disableToggles })
    this.classes = {
      ...{
        active: '--active',
        inactive: '--inactive',
        activating: '--activating',
        deactivating: '--deactivating',
      },
      ...classes
    }
    this.node = getNode(target, 'Toggleable')
    this.id = this.node.id
    this.lastUsedToggle = null
    this.clickDelayTimer = null
    this.groupNode = groupId ? document.getElementById(groupId) : null
    this.toggles = toggles || document.querySelectorAll(`[aria-controls="${this.id}"]`)
    if (this.addMatchingHashLinksToToggles) {
      this.toggles = [
        ...this.toggles,
        ...document.querySelectorAll(`a[href="/#${this.id}"]`)
      ]
    }
    if (!this.toggles.length) {
      console.warn(`Toggleable: No toggles found for ${this.id}`)
      console.trace()
    }
  }
  setClass(operation, className) {
    this.node.classList[operation](className)
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
  toggleOn() {
    this.active = true
    this.setClass('add', this.classes.active)
    this.transitionClass(this.classes.activating)
    if (this.groupNode) {
      toggleEvents.groupToggle.activeId = this.id
      this.groupNode.dispatchEvent(toggleEvents.groupToggle)
    }
  }
  toggleOff() {
    this.active = false
    this.setClass('remove', this.classes.active)
    this.transitionClass(this.classes.deactivating)
  }
  toggle() {
    if (this.active) {
      this.node.dispatchEvent(toggleEvents.toggleOff)
    } else {
      this.node.dispatchEvent(toggleEvents.toggleOn)
    }
  }
  dispatchToggle(toggle = null, event = toggleEvents.toggle) {
    this.node.dispatchEvent(event)
    if (toggle) this.lastUsedToggle = toggle
  }
  mount() {
    this.active ? this.toggleOn() : this.toggleOff()
    this.toggles.forEach(t => {
      t.setAttribute('aria-controls', this.id)
      t.addEventListener('click', () => {
        if (this.disableToggles) return
        if (t.getAttribute('href') === `/#${this.id}`) {
          this.dispatchToggle(t, toggleEvents.toggleOn)
        } else {this.dispatchToggle(t)}
      })
    })
    this.node.addEventListener('toggle',  event => {
      if (this.clickDelayTimer === null) {
        this.toggle()
      }
    })
    this.node.addEventListener('toggleOn',  event => {
      this.toggleOn()
    })
    this.node.addEventListener('toggleOff',  event => {
      this.toggleOff()
    })
    if (this.groupNode) {
      this.groupNode.addEventListener('groupToggle', event => {
        if (this.exclusiveGroup && this.active && !this.alwaysActive && event.activeId !== this.id) this.node.dispatchEvent(toggleEvents.toggleOff)
      })
    }
    if (this.toggleOffOnOutsideClick) {
      document.addEventListener('click', event => {
        if (this.active) {
          let targetInToggles = false
          this.toggles.forEach(t => {
            if (t.contains(event.target)) targetInToggles = true
          })
          if (!this.node.contains(event.target) && !targetInToggles) this.node.dispatchEvent(toggleEvents.toggleOff)
        }
      })
    }
    if (this.toggleOffOnEsc) {
      this.node.addEventListener('keydown', event => {
        if (event.key === 'Escape') this.node.dispatchEvent(toggleEvents.toggleOff)
      })
    }
    if (this.toggleOnIfUrlHashMatches) {
      if (window.location.hash === `#${this.id}`) this.node.dispatchEvent(toggleEvents.toggleOn)
      window.addEventListener('hashchange', event => {
        if (window.location.hash === `#${this.id}`) this.node.dispatchEvent(toggleEvents.toggleOn)
      })
    }
    return this
  }
}

export {Toggleable, toggleEvents}