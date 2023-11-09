export default class PropElement extends HTMLElement {
  static props = {}
  static get propToAttrName() {
    if (!Object.prototype.hasOwnProperty.call(this, '_propToAttrName')) {
      this._propToAttrName = {}
      for (let prop in this.props) {
        this._propToAttrName[prop] = this.kebabCase(prop)
      }
    }
    return this._propToAttrName
  }
  static get attrToPropName() {
    if (!Object.prototype.hasOwnProperty.call(this, '_attrToPropName')) {
      this._attrToPropName = {}
      for (let prop in this.props) {
        this._attrToPropName[this.kebabCase(prop)] = prop
      }
    }
    return this._attrToPropName
  }
  static kebabCase(string) {
    const upper = /(?<!\p{Uppercase_Letter})\p{Uppercase_Letter}|\p{Uppercase_Letter}(?!\p{Uppercase_Letter})/gu;
    return string.replace(upper, "-$&").replace(/^-/, "").toLowerCase();
  }
  static get observedAttributes(){
    return Object.keys(this.attrToPropName);
  }
  processAttributeValue(prop, val) {
    switch (typeof this.constructor.props[prop]) {
      case 'boolean':
        return val !== '0' && val !== 'false'
      case 'object':
        return JSON.parse(val || '{}')
      default:
        return val
    }
  }
  setProp(prop, value, sync = false) {
    this[prop] = value
    if (sync) {
      this.setAttribute(this.constructor.propToAttrName[prop], value)
    }
  }
  constructor() {
    super()
  }
  initProps() {
    const props = JSON.parse(this.getAttribute('props') || '{}')
    for (let prop in this.constructor.props) {
      if (prop in HTMLElement.prototype) {
        console.warn(
            `${this.constructor.name}: Property "${prop}" already exists in HTMLElement. Please choose another name. 
            If you want to use the property in its native way, there is no need to define it in the props object.`);
      }
      if (this.hasAttribute(this.constructor.propToAttrName[prop])) {
        this[prop] = this.processAttributeValue(prop, this.getAttribute(this.constructor.propToAttrName[prop]))
      } else {
        this.setProp(prop, props[prop] || this.constructor.props[prop], !!props[prop])
      }
    }
    this.removeAttribute('props')
  }
  mount() {
    return this
  }
  destroy() {
  }
  connectedCallback() {
    this.initProps()
    this.mount()
  }
  disconnectedCallback() {
    this.destroy()
  }
  attributeChangedCallback(name, oldVal, newVal) {
    if (oldVal !== newVal) {
      const prop = this.constructor.attrToPropName[name]
      this[prop] = this.processAttributeValue(prop, newVal)
    }
  }
}
