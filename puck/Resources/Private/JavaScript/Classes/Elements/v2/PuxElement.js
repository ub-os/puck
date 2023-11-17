import { kebabCase, jsonParse } from "../../../General/Utility.js";

export default class PuxElement extends HTMLElement {
  static core = {
    props: {}
  }
  static mixins = {}
  static registerMixin = (name, constructor) => {
    this.mixins[name] = constructor
  }
  static get propToAttrName() {
    if (!Object.prototype.hasOwnProperty.call(this, '_propToAttrName')) {
      this._propToAttrName = {}
      for (let prop in this.core.props) {
        this._propToAttrName[prop] = kebabCase(prop)
      }
    }
    return this._propToAttrName
  }
  static get attrToPropName() {
    if (!Object.prototype.hasOwnProperty.call(this, '_attrToPropName')) {
      this._attrToPropName = {}
      for (let prop in this.core.props) {
        this._attrToPropName[kebabCase(prop)] = prop
      }
    }
    return this._attrToPropName
  }
  static _observedAttributes = null
  static get observedAttributes(){
    if (!this._observedAttributes) {
      this._observedAttributes = Object.keys(this.attrToPropName).concat(Object.keys(this.mixins).map(mixinName => 'use-' + mixinName))
    }
    return this._observedAttributes
  }
  constructor() {
    super()
  }
  core = {
    mount: () => {},
    destroy: () => {}
  }
  mixins = {}
  convertToPropValue(prop, val) {
    switch (typeof this.constructor.core.props[prop]) {
      case 'boolean':
        return val !== '0' && val !== 'false'
      case 'object':
        return jsonParse(val)
      default:
        return val
    }
  }
  convertToAttrValue(prop, val) {
    switch (typeof this.constructor.core.props[prop]) {
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
  setCoreProp(prop, value, sync = false) {
    this.core[prop] = value
    if (sync) {
      this.setAttribute(this.constructor.propToAttrName[prop], this.convertToAttrValue(prop, value))
    }
  }
  initCore() {
    if (!this.constructor.core.prototype) return
    this.core = new this.constructor.core(this)
    const props = jsonParse(this.getAttribute('props'))
    for (let prop in this.constructor.core.props) {
      if (this.hasAttribute(this.constructor.propToAttrName[prop])) {
        this.core[prop] = this.convertToPropValue(prop, this.getAttribute(this.constructor.propToAttrName[prop]))
      } else {
        this.setCoreProp(prop, props[prop] || this.core[prop] || this.constructor.core.props[prop], !!props[prop])
      }
    }
    this.removeAttribute('props')
  }
  beforeMount() { }
  initMixins() {
    for (let mixinName in this.constructor.mixins) {
      if (this.hasAttribute('use-' + mixinName)) {
        this.addMixin(mixinName, jsonParse(this.getAttribute('use-' + mixinName)))
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
  mountCore() {
    this.core.mount()
  }
  destroyCore() {
    this.core.destroy()
  }
  destroy() {
    this.destroyMixins()
    this.destroyCore()
  }
  mount() {
    this.mountCore()
    this.mountMixins()
  }
  connectedCallback() {
    this.beforeMount()
    this.initCore()
    this.initMixins()
    this.mount()
  }
  disconnectedCallback() {
    this.destroy()
  }
  attributeChangedCallback(name, oldVal, newVal) {
    if (oldVal === newVal) return
    const prop = this.constructor.attrToPropName[name]
    const mixin = this.constructor.mixins[name]
    if (prop) {
      this.core[prop] = this.convertToPropValue(prop, newVal)
      return
    }
    if (mixin) {
      if (this.mixins[name]) {
        this.mixins[name].destroy()
      }
      this.use(name, jsonParse(newVal))
      return
    }
  }
}