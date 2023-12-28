import {jsonParse} from "~/Utility/StringUtility.js";

export default class PropLayer {
    static props = {}
    static identifier = null
    __el = null
    identifier = null
    get el() { return this.__el }
    set el(el) { this.__el = el }


    constructor(el, identifier, argProps = {}) {
        this.el = el
        this.identifier = identifier
        const attrProps = jsonParse(this.el.getAttribute('data-' + this.identifier))
        for (let prop in this.constructor.props) {
            const attr = this.constructor.writePropKey[prop]
            if (this.el.hasAttribute(attr)) {
                this.__setProp(prop, this.constructor.readProp(prop, this.el.getAttribute(attr)), false)
            } else if (Object.prototype.hasOwnProperty.call(attrProps, prop)) {
                this.__setProp(prop, attrProps[prop], true)
            } else if (Object.prototype.hasOwnProperty.call(argProps, prop)) {
                this.__setProp(prop, argProps[prop], true)
            } else {
                this.__setProp(prop, this.constructor.props[prop], false)
            }
        }
        this.el.removeAttribute('data-' + this.identifier)
    }

    __setProp(prop, val, sync = true) {
        if (val === this[`#${prop}`]) return
        if (!sync) {
            const oldVal = this[`#${prop}`]
            this[`#${prop}`] = val
            return
        }
        this[`#${prop}`] = val
        const writeVal = this.constructor.writeProp(prop, val)
        const writeName = this.constructor.writePropKey[prop]
        if (
            typeof this.constructor.props[prop] === 'object'
            && writeVal === this.constructor.writeProp(prop, this.constructor.props[prop])
        ) {
            this.el.removeAttribute(writeName)
            return
        }
        if (val === this.constructor.props[prop]) {
            this.el.removeAttribute(writeName)
            return
        }
        this.el.setAttribute(writeName, writeVal)
    }

    __attributeChanged(attrName, oldVal, newVal) {
        const prop = this.constructor.readPropKey[attrName]
        if (!prop) return
        let newPropVal;
        if (newVal === null || newVal === undefined) {
            newPropVal = this.constructor.props[prop]
        } else {
            newPropVal = this.constructor.readProp(prop, newVal)
        }
        this[`#${prop}`] = newPropVal
    }
}