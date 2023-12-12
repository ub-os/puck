import { kebabCase, jsonParse } from "~/Utility/StringUtility";
import { $$ } from "~/Utility/DomUtility";

export default class Controller {
    static props = {}
    static identifier = null
    static registerCallback() { }
    static convertToPropValue(prop, val) {
        switch (typeof this.props[prop]) {
            case 'boolean':
                return val !== '0' && val !== 'false'
            case 'object':
                return jsonParse(val)
            case 'number':
                return Number(val.replace(/_/g, ""))
            default:
                return val
        }
    }
    static convertToAttrValue(prop, val) {
        switch (typeof this.props[prop]) {
            case 'boolean':
                return val ? '' : 'false'
            case 'object':
                return JSON.stringify(val)
            case 'string':
                return val
            default:
                return val.toString()
        }
    }
    constructor(el, argProps = {}) {
        this.el = el
        const attrProps = jsonParse(this.el.getAttribute('data-' + kebabCase(this.constructor.identifier)))
        for (let prop in this.constructor.props) {
            if (this.el.hasAttribute(this.constructor.propToAttrName[prop])) {
                this.setProp(prop, this.constructor.convertToPropValue(prop, this.el.getAttribute(this.constructor.propToAttrName[prop])), false)
            } else {
                const val =
                    Object.prototype.hasOwnProperty.call(attrProps, prop)
                    ? attrProps[prop]
                    : Object.prototype.hasOwnProperty.call(argProps, prop)
                        ? argProps[prop]
                        : this.constructor.props[prop]
                this.setProp(prop, val, true)
            }
        }
        this.el.removeAttribute('data-' + kebabCase(this.constructor.identifier))
        this.initialize()
    }
    /**
     * @type {HTMLElement}
     */
    _el = null
    get el() {
        return this._el
    }
    set el(el) {
        this._el = el
    }
    get hashLinks() {
        return [...$$(`a[href*="#${this.el.id}"]`)].filter(a => a.location.pathname === window.location.pathname)
    }
/*    get ariaControls() {
        return [...$$(`[aria-controls="${this.el.id}"]`)]
    }*/
    setProp(prop, val, sync = false) {
        this[`#${prop}`] = val
        if (!sync) return
        const writeVal = this.constructor.convertToAttrValue(prop, val)
        const attrName = this.constructor.propToAttrName[prop]
        if (
            typeof this.constructor.props[prop] === 'object'
            && writeVal === this.constructor.convertToAttrValue(prop, this.constructor.props[prop])
        ) {
            this.el.removeAttribute(attrName)
            return
        }
        if (val === this.constructor.props[prop]) {
            this.el.removeAttribute(attrName)
            return
        }
        this.el.setAttribute(attrName, writeVal)
    }

    initialize()  { return this }
    connect() { return this }
    disconnect() { return this }
    attributeChanged(name, oldVal, newVal) {
        if (name === `data-${kebabCase(this.constructor.identifier)}-update`) {
            this.el.removeAttribute(name)
            this.disconnect()
            this.connect()
            return
        }
        const prop = this.constructor.attrToPropName[name]
        if (!prop) return
        let newPropVal;
        if (newVal === null || newVal === undefined) {
            newPropVal = this.constructor.props[prop]
        } else {
            newPropVal = this.constructor.convertToPropValue(prop, newVal)
        }
        if (typeof this[`${prop}Changed`] === 'function') {
            this[`${prop}Changed`](this[`#${prop}`], newPropVal)
        }
        this[`#${prop}`] = newPropVal
    }
}