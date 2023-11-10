import { kebabCase, jsonParseValue } from "../../General/Utility.js";

export default class PuxElement extends HTMLElement {
  static props = {
    asleep: false
  }
  static mixins = {}
  static registerMixin = (name, constructor) => {
    this.mixins[name] = constructor
  }
  static get propToAttrName() {
    if (!Object.prototype.hasOwnProperty.call(this, '_propToAttrName')) {
      this._propToAttrName = {}
      for (let prop in this.props) {
        this._propToAttrName[prop] = kebabCase(prop)
      }
    }
    return this._propToAttrName
  }
  static get attrToPropName() {
    if (!Object.prototype.hasOwnProperty.call(this, '_attrToPropName')) {
      this._attrToPropName = {}
      for (let prop in this.props) {
        this._attrToPropName[kebabCase(prop)] = prop
      }
    }
    return this._attrToPropName
  }
  static get observedAttributes(){
    return Object.keys(this.attrToPropName) + Object.keys(this.mixins).map(mixinName => 'use-' + mixinName);
  }
  constructor() {
    super()
  }
  _asleep = false
  mixins = {}
  get el() {
    return this
  }
  set asleep(value) {
    if (!this.asleep === !value) return
    if (value) {
      this._asleep = true
      this.setAttribute('asleep', '')
      this.destroy()
    } else {
      this._asleep = false
      this.removeAttribute('asleep')
      this.mount()
    }
  }
  get asleep() {
    return this._asleep
  }
  convertToPropValue(prop, val) {
    switch (typeof this.constructor.props[prop]) {
      case 'boolean':
        return val !== '0' && val !== 'false'
      case 'number':
        return parseFloat(val)
      case 'object':
        return jsonParseValue(val)
      default:
        return val
    }
  }
  convertToAttrValue(prop, val) {
    switch (typeof this.constructor.props[prop]) {
      case 'boolean':
        return val ? '' : 'false'
      case 'object':
        return JSON.stringify(val)
      default:
        return val
    }
  }
  setAttributes(keyValues = {})  {
    for (let key in keyValues) {
      this.setAttribute(key, keyValues[key])
    }
  }
  setProp(prop, value, sync = false) {
    this[prop] = value
    if (sync) {
      this.setAttribute(this.constructor.propToAttrName[prop], this.convertToAttrValue(prop, value))
    }
  }
  initProps() {
    const props = jsonParseValue(this.getAttribute('props'))
    for (let prop in this.constructor.props) {
      if (prop in HTMLElement.prototype) {
        console.warn(
            `${this.constructor.name}: Property "${prop}" already exists in HTMLElement. Please choose another name. 
            If you want to use the property in its native way, there is no need to define it in the props object.`);
      }
      if (this.hasAttribute(this.constructor.propToAttrName[prop])) {
        this[prop] = this.convertToPropValue(prop, this.getAttribute(this.constructor.propToAttrName[prop]))
      } else {
        this.setProp(prop, props[prop] || this[prop] || this.constructor.props[prop], !!props[prop])
      }
    }
    this.removeAttribute('props')
  }
  beforeMount() {
  }
  initMixins() {
    for (let mixinName in this.constructor.mixins) {
      if (this.hasAttribute('use-' + mixinName)) {
        this.addMixin(mixinName, jsonParseValue(this.getAttribute('use-' + mixinName)))
      }
    }
  }
  addMixin(mixinName, props = {}) {
    this.mixins[mixinName] = new this.constructor.mixins[mixinName](this, props)
    return this.mixins[mixinName]
  }
  use(mixinName, props = {}) {
    return this.addMixin(mixinName, props)
  }
  mountMixins() {
    for (let mixinName in this.mixins) {
        this.mixins[mixinName].mount()
    }
  }
  destroyMixins() {
    for (let mixinName in this.mixins) {
      this.mixins[mixinName].destroy()
    }
  }
  mount() {
    return this
  }
  destroy() {
  }
  destroyAll() {
    this.destroyMixins()
    this.destroy()
  }
  mountAll() {
    this.mount()
    this.mountMixins()
  }
  connectedCallback() {
    this.beforeMount()
    this.initProps()
    this.initMixins()
    if (this.asleep) return
    this.mountAll()
  }
  disconnectedCallback() {
    this.destroyAll()
  }
  attributeChangedCallback(name, oldVal, newVal) {
    if (oldVal === newVal) return
    const prop = this.constructor.attrToPropName[name]
    const mixin = this.constructor.mixins[name]
    if (prop) {
      this[prop] = this.convertToPropValue(prop, newVal)
      return
    }
    if (mixin) {
      if (this.mixins[name]) {
        this.mixins[name].destroy()
      }
      this.use(name, jsonParseValue(newVal))
      return
    }
  }
}

