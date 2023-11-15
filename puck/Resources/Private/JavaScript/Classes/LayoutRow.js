
import AbstractComponent from "./AbstractComponent.js";

export default class LayoutRow extends AbstractComponent {
  constructor(target, { ...options } = {}) {
    super(target, options)
  }
  mount() {
    if (this.element.children.length < 3) return this
    this.element.setAttribute('role', 'list')
    this.element.childNodes.forEach( child => {
      if (child.nodeType !== 1) return
      child.setAttribute('role', 'listitem')
    })
    return this
  }
}