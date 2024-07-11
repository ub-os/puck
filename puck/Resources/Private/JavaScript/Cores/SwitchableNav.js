import { $, $$, jsx } from '~/_jcores/Utility/DomUtility'
import { ResizeManager } from "~/Service/ObserverCollector"
import Core from "~/_jcores/Core"
import htmx from '~/htmx'

/**
 * @property {Modal} modalCore
 * @property {HTMLElement} templateUnit
 */
export default class SwitchableNav extends Core {
  static attributes = {
    breakpoint: 800,
  }
  static units = ['template']
  templateWasAdded = false
  stateSwitch() {
    if (!this.templateWasAdded && (window.innerWidth < this.breakpoint)) {
      this.addNavFromTemplate()
      this.state = 'switched'
      this.templateWasAdded = true
      ResizeManager.remove(`switchable-nav-${this.el.id}`)
    }
  }

  addNavFromTemplate() {
    const clone = this.templateUnit.content.cloneNode(true)
    this.el.parentNode.appendChild(clone)
    htmx.process(this.el.parentNode)
  }

  initialize() {
  }

  connect() {
    if (this.templateWasAdded) return
    window.requestAnimationFrame(() => {
      this.stateSwitch()
      window.requestAnimationFrame(() => {
        ResizeManager.addById(`switchable-nav-${this.el.id}`, document.body, this.stateSwitch.bind(this))
      })
    })
    return this
  }

  disconnect() {
    this.listeners.removeAll()
    ResizeManager.remove(`switchable-nav-${this.el.id}`)
  }
}
