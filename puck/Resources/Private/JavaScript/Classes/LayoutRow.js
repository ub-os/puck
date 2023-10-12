
import AbstractComponent from "./AbstractComponent.js";

export default class LayoutRow extends AbstractComponent{
  constructor(target, { ...options } = {}) {
    super(target, options)
  }
  changeTagName(element, newTagName) {
    const newElement = document.createElement(newTagName)
    for (let attribute of element.attributes) {
      newElement.attributes.setNamedItem(attribute.cloneNode());
    }
    newElement.innerHTML = element.innerHTML
    element.parentNode.insertBefore(newElement, element)
    element.remove()
    return newElement
  }

  mount() {
    if (this.element.children.length < 3) {
      this.element = this.changeTagName(this.element, 'div')
      this.element.childNodes.forEach( child => {
        if (child.tagName === 'LI') this.changeTagName(child, 'div')
      })
    }
    return this
  }
}