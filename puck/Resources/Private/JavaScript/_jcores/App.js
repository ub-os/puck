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
    #customTagSelector = ''
    #customTags = []
    config = config

    get connectAttr() {
        return config.attributePrefix + config.connectAttribute
    }
    get handlerAttr() {
        return config.attributePrefix + config.handlerAttribute
    }
    connect$(el = document) {
        return el.querySelectorAll(`[${config.attributePrefix}${config.connectAttribute}]${this.#customTagSelector}`)
    }
    handler$(el = document) {
        return el.querySelectorAll(`[${config.attributePrefix}${config.handlerAttribute}]`)
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
        this.#customTagSelector += `, ${config.customElementPrefix}${identifier}`
        this.#customTags[(config.customElementPrefix + identifier).toUpperCase()] = identifier
        customElements.define(config.customElementPrefix + identifier, class extends HTMLElement {
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
    
    getAspects(el) {
        return el?.jc_aspects
    }

    getAspect(el, aspectName) {
        return el?.get(aspectName)
    }

    connect() {
        this.connectNode(document.body)
        if (config.observeChildList) {
            this.childListObserver.observe(document.body, { childList: true, subtree: true })
        }
        if (config.observeAttributes) {
            this.attributeObserver.observe(document.body, { attributes: true, attributeFilter: [this.connectAttr, this.handlerAttr], subtree: true })
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
            if (mutation.attributeName === this.connectAttr) {
                this.disconnectSubEl(mutation.target)
                this.disconnectHostEl(mutation.target)
                this.connectHostEl(mutation.target)
                this.connectSubEl(mutation.target)
                return
            }
            if (mutation.attributeName === this.handlerAttr) {
                this.disconnectHandlerEl(mutation.target)
                this.connectHandlerEl(mutation.target)
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
        const connectNodes = this.connect$(node)
        if (node.hasAttribute(this.connectAttr) || node.tagName.startsWith(config.customElementPrefix)) this.connectHostEl(node)
        connectNodes.forEach(child => this.connectHostEl(child))
        if (node.hasAttribute(this.connectAttr)) this.connectSubEl(node)
        connectNodes.forEach(child => this.connectSubEl(child))
        if (node.hasAttribute(this.handlerAttr)) this.connectHandlerEl(node)
        this.handler$(node).forEach(child => this.connectHandlerEl(child))
    }
    disconnectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        if (node.hasAttribute(this.handlerAttr)) this.disconnectHandlerEl(node)
        this.handler$(node).forEach(child => this.disconnectHandlerEl(child))
        const connectNodes = this.connect$(node)
        if (node.hasAttribute(this.connectAttr)) this.disconnectSubEl(node)
        connectNodes.forEach(child => this.disconnectSubEl(child))
        if (node.hasAttribute(this.connectAttr)) this.disconnectHostEl(node)
        connectNodes.forEach(child => this.disconnectHostEl(child))
    }

    connectHandlerEl(el) {
        el.getAttribute(this.handlerAttr).split(' ').forEach(descriptor => {
            new ElementEventHandler(el, descriptor, this.eventRegistry)
        })
        el.jc_handlers?.forEach(handler => {
            handler.connect()
        })
    }
    disconnectHandlerEl(el) {
        el.jc_handlers?.forEach(handler => {
            handler.disconnect()
        })
        el.jc_handlers = null
    }

    connectSubEl(el) {
        let identifiers = (el.getAttribute(this.connectAttr) ?? '')
            .split(' ').filter(identifier => identifier && identifier.includes('.'))
        identifiers.forEach(descriptor => {
            new ElementConnection(el, descriptor)
        })
        el.jc_connections?.forEach(connection => {
            connection.connect()
        })
    }
    disconnectSubEl(el) {
        el.jc_connections?.forEach(connection => {
            connection.disconnect()
        })
        el.jc_connections = null
    }

    connectHostEl(el) {
        let identifiers = (el.getAttribute(this.connectAttr) ?? '')
                .split(' ').filter(identifier => identifier && !identifier.includes('.'))
        if (this.#customTags[el.tagName]) {
            identifiers.push(this.#customTags[el.tagName])
        }
        identifiers.forEach(identifier => {
            this.injectAspect(el, identifier)
        })
        if (el.jc_aspects && config.observeAspectAttributes) this.aspectObserver.observe(el, { attributes: true, attributeOldValue: true })
        el.jc_aspects?.forEach(aspect => {
            if (!aspect.__connected && !aspect.asleep) {
                aspect.connect()
                aspect.__connected = true
            }
        })
    }
    disconnectHostEl(el) {
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
            if (!mutation.attributeName.startsWith(config.attributePrefix)) return
            const newVal = mutation.target.getAttribute(mutation.attributeName)
            mutation.target.jc_aspects?.forEach(aspect => {
                if (mutation.attributeName == `${config.attributePrefix}${aspect.__identifier}-reconnect`) {
                    window.requestAnimationFrame(() => {
                        aspect.disconnect()
                        aspect.connect()
                        mutation.target.removeAttribute(`${config.attributePrefix}${aspect.__identifier}-reconnect`)
                    })
                }
                aspect.constructor.attributeSyncer.attributeChanged(aspect, mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })
}

export default new App()