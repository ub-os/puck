import {camelCase, kebabCase} from "~/_jcores/Utils.js";
import AspectAttributeSyncer from "~/_jcores/AspectAttributeSyncer.js";
import config from "./Config"


export default class Registry {
    aspectRegister = new Map()
    connectedCallbackRegister = new Map()
    customTagSelector = ''
    customTags = []
    registerAspect(identifier, constructor) {
        if (typeof identifier == 'object') {
            Object.entries(identifier).forEach(([key, value]) => {
                this.registerAspect(kebabCase(key), value)
            })
            return
        }
        if (!constructor.shouldLoad()) return
        this.processAspect(identifier, constructor)
        this.aspectRegister.set(identifier, constructor)
        constructor.afterLoad()
    }
    registerAspectCustomElement(identifier) {
        if (Array.isArray(identifier)) {
            identifier.forEach(value => {
                this.registerAspectCustomElement(value)
            })
            return
        }
        if (!this.aspectRegister.has(identifier)) {
            console.warn(`Aspect ${identifier} not found in register, skipping custom element registration.`)
            return
        }
        this.customTagSelector += `, ${config.customElementPrefix}${identifier}`
        this.customTags[(config.customElementPrefix + identifier).toUpperCase()] = identifier
        customElements.define(config.customElementPrefix + identifier, class extends HTMLElement {
            constructor() {
                super()
            }
        })
    }
    registerConnectedCallback(selector, callback) {
        if (typeof selector == 'object') {
            Object.entries(selector).forEach(([key, value]) => {
                this.registerConnectedCallback(key, value)
            })
            return
        }
        this.connectedCallbackRegister.set(selector, callback)
    }
    processAspect(identifier, constructor) {
        constructor.identifier = identifier
        constructor.attributeSyncer = new AspectAttributeSyncer(identifier, constructor, constructor.attributes)
        const convertToObj = arr => {
            return arr.reduce((result, key) => {
                if (typeof key !== 'string') {
                    console.warn(`Invalid identifier ${key} on ${identifier}, skipping.`)
                    return result
                }
                result[key] = {}
                return result
            }, {})
        }
        if (Array.isArray(constructor.injectedAspects)) {
            constructor.injectedAspects = convertToObj(constructor.injectedAspects)
        }

        for (let attrKey of Object.keys(constructor.attributes)) {
            Object.defineProperty(constructor.prototype, attrKey, {
                get() {
                    return this[`#${attrKey}`]
                },
                set(val) {
                    this.constructor.attributeSyncer.setAttribute(this, attrKey, val, true)
                }
            })
        }
        for (let elementName of constructor.connectedElements) {
            Object.defineProperty(constructor.prototype, `${elementName}Elements`, {
                get() {
                    return this.__internal.elements.get(elementName)
                }
            })
            Object.defineProperty(constructor.prototype, `${elementName}Element`, {
                get() {
                    return this.__internal.elements.get(elementName).values()?.next()?.value
                },
            })
        }
        for (let injectIdentifier of Object.keys(constructor.injectedAspects)) {
            Object.defineProperty(constructor.prototype, `${camelCase(injectIdentifier)}Aspect`, {
                get() {
                    return this.el.nxs_aspects.get(injectIdentifier)
                },
            })
        }
    }
}