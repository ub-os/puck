import { camelCase, kebabCase } from "./Utils"
import AspectAttributeSyncer from "./AspectAttributeSyncer"
import ElementAspect from "./ElementAspect"
import ElementConnection from "./ElementConnection"
import ElementEventHandler from "./ElementEventHandler"
import config from "./Config"

class App {
    #idx = 0
    aspectRegistry = {}
    eventRegistry = {}
    connectedCallbackRegistry = {}
    #aspectCustomElementSelector = ''
    #customElementTags = []
    config = config

    get connectAttribute() {
        return this.config.attributePrefix + this.config.connectAttribute
    }
    get handlerAttribute() {
        return this.config.attributePrefix + this.config.handlerAttribute
    }
    get connectSelector() {
        return `[${this.config.attributePrefix}${this.config.connectAttribute}]${this.#aspectCustomElementSelector}`
    }
    get handlerSelector() {
        return `[${this.config.attributePrefix}${this.config.handlerAttribute}]`
    }

    registerAspect(identifier, constructor) {
        if (typeof identifier == 'object') {
            Object.entries(identifier).forEach(([key, value]) => {
                this.registerAspect(kebabCase(key), value)
            })
            return
        }
        this.processAspect(identifier, constructor)
        constructor.registerCallback()
        this.aspectRegistry[identifier] = constructor
    }

    registerAspectCustomElement(identifier) {
        if (Array.isArray(identifier)) {
            identifier.forEach(value => {
                this.registerAspectCustomElement(value)
            })
            return
        }
        if (!this.aspectRegistry[identifier]) {
            console.warn(`Aspect ${identifier} not found in register, skipping custom element registration.`)
            return
        }
        this.#aspectCustomElementSelector += `, ${this.config.customElementPrefix}${identifier}`
        this.#customElementTags[(this.config.customElementPrefix + identifier).toUpperCase()] = identifier
        customElements.define(this.config.customElementPrefix + identifier, class extends HTMLElement {
            constructor() {
                super()
            }
        })
    }

    registerEvent(eventType, options) {
        if (typeof eventType == 'object') {
            Object.entries(eventType).forEach(([key, value]) => {
                this.registerEvent(key, value)
            })
            return
        }
        this.eventRegistry[eventType] = options
    }
    registerConnectedCallback(selector, callback) {
        if (typeof selector == 'object') {
            Object.entries(selector).forEach(([key, value]) => {
                this.registerConnectedCallback(key, value)
            })
            return
        }
        this.connectedCallbackRegistry[selector] = callback
    }
    processAspect(identifier, constructor) {
        constructor.identifier = identifier
        constructor.attributes = {
            ...ElementAspect.attributes,
            ...constructor.attributes,
        }
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
        if (Array.isArray(constructor.connectedElements)) {
            constructor.connectedElements = convertToObj(constructor.connectedElements)
        }
        if (Array.isArray(constructor.injectedAspects)) {
            constructor.injectedAspects = convertToObj(constructor.injectedAspects)
        }
        for (let elementName of Object.keys(constructor.connectedElements)) {
            Object.defineProperty(constructor.prototype, `${elementName}Elements`, {
                get() {
                    if (!this[`#${elementName}Elements`]) {
                        this[`#${elementName}Elements`] = new Set()
                    }
                    return this[`#${elementName}Elements`]
                }
            })
            Object.defineProperty(constructor.prototype, `${elementName}Element`, {
                get() {
                    return this[`${elementName}Elements`].values()?.next()?.value
                },
            })
        }
        for (let injectIdentifier of Object.keys(constructor.injectedAspects)) {
            Object.defineProperty(constructor.prototype, `${camelCase(injectIdentifier)}Aspect`, {
                get() {
                    return this.el.jc_aspects.get(injectIdentifier)
                },
            })
        }
    }

    get connectElements() {
        return document.querySelectorAll(this.connectSelector)
    }
    get handlerElements() {
        return document.querySelectorAll(this.handlerSelector)
    }

    getAspects(el) {
        return el?.jc_aspects
    }

    getAspect(el, aspectName) {
        return el?.get(aspectName)
    }

    connect() {
        this.connectNode(document.body)
        if (this.config.observeChildList) {
            this.childListObserver.observe(document.body, { childList: true, subtree: true })
        }
        if (this.config.observeAttributes) {
            this.attributeObserver.observe(document.body, { attributes: true, attributeFilter: [this.connectAttribute, this.handlerAttribute], subtree: true })
        }
    }

    disconnect() {
        this.childListObserver.takeRecords()
        this.attributeObserver.takeRecords()
        this.aspectObserver.takeRecords()
        this.childListObserver.disconnect()
        this.attributeObserver.disconnect()
        this.aspectObserver.disconnect()
        this.disconnectNode(document.body)
        this.#idx = 0
    }

    childListObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.removedNodes.forEach(node => {
                this.disconnectNode(node)
            })
            mutation.addedNodes.forEach(node => {
                this.connectNode(node)
            })
        })
    })

    attributeObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.attributeName === this.connectAttribute) {
                this.disconnectAspectElement(mutation.target)
                this.connectAspectElement(mutation.target)
                return
            }
            if (mutation.attributeName === this.handlerAttribute) {
                this.disconnectHandlerElement(mutation.target)
                this.connectHandlerElement(mutation.target)
                return
            }
        })
    })

    connectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        for (let [selector, callback] of Object.entries(this.connectedCallbackRegistry)) {
            if (node.matches(selector)) {
                callback(node)
            }
            node.querySelectorAll(selector).forEach(el => callback(el))
        }
        if (node.hasAttribute(this.connectAttribute) || node.tagName.startsWith(config.customElementPrefix)) this.connectAspectElement(node)
        node.querySelectorAll(this.connectSelector).forEach(child => this.connectAspectElement(child))
        if (node.hasAttribute(this.handlerAttribute)) this.connectHandlerElement(node)
        node.querySelectorAll(this.handlerSelector).forEach(child => this.connectHandlerElement(child))
    }

    disconnectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        if (node.hasAttribute(this.handlerAttribute)) this.disconnectHandlerElement(node)
        node.querySelectorAll(this.handlerSelector).forEach(child => this.disconnectHandlerElement(child))
        if (node.hasAttribute(this.connectAttribute)) this.disconnectAspectElement(node)
        node.querySelectorAll(this.connectSelector).forEach(child => this.disconnectAspectElement(child))
    }

    connectHandlerElement(el) {
        el.getAttribute(this.handlerAttribute).split(' ').forEach(descriptor => {
            new ElementEventHandler(el, descriptor, this.eventRegistry)
        })
        el.jc_handlers?.forEach(handler => {
            handler.connect()
        })
    }

    disconnectHandlerElement(el) {
        el.jc_handlers?.forEach(handler => {
            handler.disconnect()
        })
        el.jc_handlers = null
    }

    connectAspectElement(el) {
        let identifiers = (el.getAttribute(this.connectAttribute) ?? '').split(' '),
            aspectIdentifiers = [],
            aspectElementIdentifiers = []

        identifiers.forEach(identifier => {
            if (identifier.includes('.')) aspectElementIdentifiers.push(identifier)
            else if (identifier) aspectIdentifiers.push(identifier)
        })

        if (this.#customElementTags[el.tagName]) {
            aspectIdentifiers = aspectIdentifiers.push(this.#customElementTags[el.tagName])
        }
        aspectIdentifiers.forEach(identifier => {
            this.injectAspect(el, identifier)
        })
        if (el.jc_aspects && config.observeAspectAttributes) this.aspectObserver.observe(el, { attributes: true, attributeOldValue: true })
        el.jc_aspects?.forEach(aspect => {
            if (aspect.__connected || aspect.asleep) return
            aspect.connect()
            aspect.__connected = true
        })
        aspectElementIdentifiers.forEach(descriptor => {
            new ElementConnection(el, descriptor)
        })
        el.jc_connections?.forEach(connection => {
            connection.connect()
        })
    }
    disconnectAspectElement(el) {
        el.jc_connections?.forEach(connection => {
            connection.disconnect()
        })
        el.jc_connections = null
        el.jc_aspects?.forEach(aspect => {
            if (!aspect.__connected || aspect.asleep) return
            aspect.disconnect()
            aspect.__connected = false
        })
        el.jc_aspects = null
    }

    injectAspect(el, identifier, attributes = {}) {
        if (!this.aspectRegistry[identifier]) {
            console.warn(`Aspect ${identifier} not found in register, skipping.`)
            return
        }
        if (el.jc_aspects?.has(identifier)) {
            console.warn(`Aspect ${identifier} already used on this element, overriding.`)
        }
        Object.entries(this.aspectRegistry[identifier].injectedAspects).forEach(([injectIdentifier, injectAttributes]) => {
            this.injectAspect(el, injectIdentifier, injectAttributes)
        })
        const aspect = new this.aspectRegistry[identifier](el, attributes)
        aspect.app = this
    }

    aspectObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (!mutation.attributeName.startsWith(this.config.attributePrefix)) return
            const newVal = mutation.target.getAttribute(mutation.attributeName)
            mutation.target.jc_aspects?.forEach(aspect => {
                if (mutation.attributeName == `${this.config.attributePrefix}${aspect.__identifier}-reconnect`) {
                    window.requestAnimationFrame(() => {
                        aspect.disconnect()
                        aspect.connect()
                        mutation.target.removeAttribute(`${this.config.attributePrefix}${aspect.__identifier}-reconnect`)
                    })
                }
                aspect.constructor.attributeSyncer.attributeChanged(aspect, mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })
}

export default new App()