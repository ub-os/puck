import { $, $$, $id, $target } from '~/Utility/DomUtility'
import { Aspect } from "~/_nexus"
import EventHandlerSet from "~/Helper/EventHandlerSet"

/**
 * @property {Map} toggleElements
 */
export default class Showable extends Aspect {
  static elements = ['toggle']
  static attributes = {
    active: false,
    groupId: '',
    duration: 0,
    trigger: 'click',
    activeClass: '--active',
    activatingClass: '--activating',
    deactivatingClass: '--deactivating',
    // only one showable in group can be active
    exclusiveGroup: true,
    // disable toggles
    disableToggles: false,
    // set state classes on documentElement
    documentClassing: true,
    // if url hash matches elements id on connect trigger show
    // clicking an anchor with hash matching elements id will trigger show
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
    if (this.documentClassing) {
      document.documentElement.classList[operation](`--${this.el.id}-${this.token}${className}`)
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
  show({ transition = true, trigger = '' } = {}) {
    this.dispatch('show', { detail: { transition, trigger } })
  }
  hide({ transition = true, changeUrlHash = true, trigger = '' } = {}) {
    this.dispatch('hide', { detail: { transition, changeUrlHash, trigger } })
  }
  toggle(detail = {}, event = {}) {
    if (this.active && (!this.switchToggles || (this.switchToggles && event.handlerTarget === this.lastUsedToggle))) {
      this.hide(detail)
      this.lastUsedToggle = event.handlerTarget
    } else if (!this.active) {
      this.show(detail)
      this.lastUsedToggle = event.handlerTarget
    }
  }
  onShow(event) {
    this.active = true
    this.setClass('add', this.activeClass)
    if (event.detail.transition) this.transitionClass(this.activatingClass)
    if (this.groupEl) {
      this.dispatch('toggle-group', { target: this.groupEl, detail: {showTarget: this.el} })
    }
    return true
  }
  onHide(event) {
    this.active = false
    this.setClass('remove', this.activeClass)
    if (event.detail.transition) this.transitionClass(this.deactivatingClass)
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
    if (event.detail.changeUrlHash && this.urlHashRemove && window.location.hash.split('?')[0] === `#${this.el.id}`) {
      history.replaceState(history.state, document.title, location.href.replace(`#${this.el.id}`, '')) // remove hash from url
    }
    return true
  }

  toggleElementConnected(el) {
    el.ariaControls = this.el.id
    if (this.active) {
      el.classList.add(this.activeClass)
    }
  }

  connected() {
    this.groupEl = this.groupId ? $id(this.groupId) : null
    if (this.pauseMediaOnHide) {
      this.mediaChildren = this.el.$$('video, audio')
    }
    if (this.reloadIframeOnHide) {
      this.iframeChildren = this.el.$$('iframe')
    }
    this.active ? this.onShow({ detail: {transition: false} }) : this.onHide({ detail: { transition: false, changeUrlHash: false }})

    this.handlerSet.add(this.el, `${this.token}:show`, event => window.requestAnimationFrame(() => {
      if (this.active || event.defaultPrevented) return
      this.onShow(event)
    }))
    this.handlerSet.add(this.el, `${this.token}:hide`, event => window.requestAnimationFrame(() => {
      if (!this.active || event.defaultPrevented) return
      this.onHide(event)
    }))

    if (this.groupEl) {
      this.handlerSet.add(this.groupEl, `${this.token}:toggle-group`, event => {
        if (event.defaultPrevented) return
        if (this.exclusiveGroup && this.active && !this.alwaysActive && event.detail.showTarget !== this.el) {
          this.hide({ trigger: 'exclusiveGroup' })
        }
      })
    }
    if (this.outClickHide) {
      const target = this.outTarget ? $target(this.outTarget, 'Showable') : this.el
      this.handlerSet.add(document, 'click', event => {
        if (this.active && !target.contains(event.target)) this.hide({ trigger: 'outClick' })
      })
    }
    if (this.selfClickHide) {
      this.handlerSet.add(this.el, 'click', event => {
        if (this.active && event.target === this.el) this.hide({ trigger: 'selfClick' })
      })
    }
    if (this.escHide) {
      this.handlerSet.add(this.el, 'keydown', event => {
        if (event.key === 'Escape') this.hide({ trigger: 'esc' })
      })
    }
    if (this.scrollHide) {
      this.handlerSet.add(window, 'scroll', () => {
        if (this.active) this.hide({ trigger: 'scroll' })
      }, {passive: true})
    }
    if (this.clickHideSelector) {
      this.handlerSet.addDelegate(this.el, this.clickHideSelector, 'click', event => {
        if (this.active) this.hide({ trigger: 'clickOnSelector:'+this.clickHideSelector })
      })
    }
    if (this.urlHashShow) {
      if (window.location.hash.split('?')[0] === `#${this.el.id}`) this.show({ trigger: 'urlHash' })
      this.handlerSet.add(this.el, 'hash-link-click', event => {
        this.lastUsedToggle = event.detail.linkElement
        this.show({ trigger: 'urlHash' })
      })
    }
    return this
  }
  disconnected() {
    this.hide({ transition: false, changeUrlHash: false })
    this.handlerSet.clear()
  }
}
