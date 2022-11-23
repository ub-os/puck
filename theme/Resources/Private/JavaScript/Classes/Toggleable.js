
const toggleEvents = {
    toggle: new Event('toggle'),
    toggleOn: new Event('toggleOn'),
    toggleOff: new Event('toggleOff'),
    groupToggle: new Event('groupToggle'),
}

class Toggleable {
  constructor(
      target,
      {
        toggles,
        active = false,
        alwaysActive = false,
        activeExclusionId = null,
        clickDelay = 0,
        classes = {},
        toggleOffOnOutsideClick = false,
        toggleOffOnEsc = true,
        toggleOnIfUrlHashMatches = true,
        enableToggles = true,
      }){
    Object.assign(this, { active, alwaysActive, activeExclusionId, clickDelay, toggleOffOnOutsideClick, toggleOffOnEsc, toggleOnIfUrlHashMatches, enableToggles })
    this.classes = {
      ...{
        active: '--active',
        inactive: '--inactive',
        activating: '--activating',
        deactivating: '--deactivating',
      },
      ...classes
    }

    if (target instanceof Element) {
      this.node = target
    } else if (typeof target === 'string' && document.getElementById(target)) {
      this.node = document.getElementById(target)
    } else {
      console.error('Toggleable: No valid element or id for root node provided')
      console.trace()
    }

    this.id = this.node.id
    this.lastUsedToggle = null
    this.clickDelayTimer = null
    this.groupNode = activeExclusionId ? document.getElementById(activeExclusionId) : null

    this.toggles = toggles || document.querySelectorAll(`[aria-controls="${this.id}"]`)
    if (!this.toggles.length) {
      console.warn(`Toggleable: No toggles found for ${this.id}`)
      console.trace()
    }

  }
  setClass(operation, className) {
    this.node.classList[operation](className)
    document.body.classList[operation](`--${this.id}${className}`)
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
    if (this.active && !this.alwaysActive) {
      this.node.dispatchEvent(toggleEvents.toggleOff)
    } else {
      this.node.dispatchEvent(toggleEvents.toggleOn)
    }
  }
  dispatchToggle(toggle = null) {
    this.node.dispatchEvent(toggleEvents.toggle)
    if (toggle) this.lastUsedToggle = toggle
  }
  mount() {
    this.active ? this.toggleOn() : this.toggleOff()
    this.toggles.forEach(t => {
      t.ariaControls = this.node.id
      t.addEventListener('click', () => {
        if (this.enableToggles) this.dispatchToggle(t)
      })
    })
    this.node.addEventListener('toggle',  (event) => {
      if (this.clickDelayTimer === null) {
        this.toggle()
      }
    })
    this.node.addEventListener('toggleOn',  (event) => {
      this.toggleOn()
    })
    this.node.addEventListener('toggleOff',  (event) => {
      this.toggleOff()
    })
    if (this.groupNode) {
      this.groupNode.addEventListener('groupToggle',  (event) => {
        if (event.activeId !== this.id && this.active) this.toggleOff()
      })
    }
    if (this.toggleOffOnOutsideClick) {
      document.addEventListener('click', (event) => {
        let targetInToggles = false
        this.toggles.forEach(t => {
          if (t.contains(event.target)) targetInToggles = true
        })
        if (this.active && !this.node.contains(event.target) && !targetInToggles) this.toggleOff()
      })
    }
    if (this.toggleOffOnEsc) {
      this.node.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') this.toggleOff()
      })
    }
    if (this.toggleOnIfUrlHashMatches) {
      if (window.location.hash === `#${this.id}`) this.toggleOn()
      window.addEventListener('hashchange', (event) => {
        if (window.location.hash === `#${this.id}`) this.toggleOn()
      })
    }
    return this
  }
}

export {Toggleable, toggleEvents}
