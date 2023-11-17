import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior"

export default class LayoutRow extends AbstractBehavior {
  mount() {
    if (this.el.children.length < 3) return this
    this.el.setAttribute('role', 'list')
    this.el.childNodes.forEach( child => {
      if (child.nodeType !== 1) return
      child.setAttribute('role', 'listitem')
    })
    return this
  }
}