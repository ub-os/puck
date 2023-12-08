import Controller from "~/Application/Controller.js";

export default class LayoutRow extends Controller {
  connect() {
    if (this.el.children.length < 3) return this
    this.el.setAttribute('role', 'list')
    this.el.childNodes.forEach( child => {
      if (child.nodeType !== 1) return
      child.setAttribute('role', 'listitem')
    })
    return this
  }
}