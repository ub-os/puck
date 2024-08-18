import { kebabCase } from "./Utils"
import config from "./Config"

export default class AspectAttributeSyncer {
    identifier = null
    attributes = {}
    attributeKeyMap = {}
    convertKey = attrKey => {
        return `${config.attributePrefix}${this.identifier}.${kebabCase(attrKey)}`
    }
    constructor(identifier, constructor, attributes = {}) {
        this.identifier = identifier
        this.attributes = attributes
        Object.keys(attributes).forEach(attrKey => {
            if (!this.attributeKeyMap[attrKey]) {
                this.attributeKeyMap[attrKey] = this.convertKey(attrKey)
            }
            this.attributeKeyMap[this.attributeKeyMap[attrKey]] = attrKey
            Object.defineProperty(constructor.prototype, attrKey, {
                get() {
                    return this[`#${attrKey}`]
                },
                set(val) {
                    this.constructor.attributeSyncer.setAttribute(this, attrKey, val, true)
                }
            })
        })
    }

    write(attrKey, val) {
        if (typeof this.attributes[attrKey] == 'boolean') {
            return val ? '' : 'false'
        }
        try { return JSON.stringify(val) } catch { return val }
    }
    read(attrKey, val) {
        if (typeof this.attributes[attrKey] == 'boolean') {
            return val !== '0' && val !== 'false'
        }
        try { return JSON.parse(val) } catch { return val }
    }

    initialize(instance, argAttributes = {}) {
        const attrAttributes = JSON.parse(instance.el.getAttribute(config.attributePrefix + this.identifier) || '{}')
        for (let attrKey in this.attributes) {
            const dataAttr = this.attributeKeyMap[attrKey]
            if (instance.el.hasAttribute(dataAttr)) {
                this.setAttribute(instance, attrKey, this.read(attrKey, instance.el.getAttribute(dataAttr)), false)
            } else if (Object.prototype.hasOwnProperty.call(attrAttributes, attrKey)) {
                this.setAttribute(instance, attrKey, attrAttributes[attrKey], true)
            } else if (Object.prototype.hasOwnProperty.call(argAttributes, attrKey)) {
                this.setAttribute(instance, attrKey, argAttributes[attrKey], true)
            } else {
                this.setAttribute(instance, attrKey, this.attributes[attrKey], false)
            }
        }
        instance.el.removeAttribute(config.attributePrefix + this.identifier)
    }

    setAttribute(instance, attrKey, val, sync = true) {
        if (val === instance[`#${attrKey}`]) return
        if (!sync) {
            const oldVal = instance[`#${attrKey}`]
            instance[`#${attrKey}`] = val
            if (instance.__initialized && typeof instance[`${attrKey}Changed`] === 'function') {
                instance[`${attrKey}Changed`](oldVal, val)
            }
            return
        }
        instance[`#${attrKey}`] = val
        const writeVal = this.write(attrKey, val)
        const writeName = this.attributeKeyMap[attrKey]
        if (
            typeof this.attributes[attrKey] === 'object'
            && writeVal === this.write(attrKey, this.attributes[attrKey])
        ) {
            instance.el.removeAttribute(writeName)
            return
        }
        if (val === this.attributes[attrKey]) {
            instance.el.removeAttribute(writeName)
            return
        }
        instance.el.setAttribute(writeName, writeVal)
    }

    attributeChanged(instance, name, oldVal, newVal) {
        const attrKey = this.attributeKeyMap[name]
        if (!attrKey) {
            instance.attributeChanged(name, oldVal, newVal)
            return
        }
        let newReadVal
        if (newVal === null || newVal === undefined) {
            newReadVal = this.attributes[attrKey]
        } else {
            newReadVal = this.read(attrKey, newVal)
        }
        instance[`#${attrKey}`] = newReadVal
        if (instance.__initialized && typeof instance[`${attrKey}Changed`] === 'function') {
            const oldReadVal = oldVal === undefined || oldVal === null ? this.attributes[attrKey] : this.read(attrKey, oldVal)
            instance[`${attrKey}Changed`](oldReadVal, newReadVal)
        }
    }
}