import { camelCase, kebabCase } from "./Utils"
import AspectAttributeSyncer from "./AspectAttributeSyncer"
import ElementAspect from "./ElementAspect"
import ElementConnection from "./ElementConnection"
import ElementEventHandler from "./ElementEventHandler"
import config from "./Config"

class App {
    aspectRegistry = {}
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
        if (!constructor.shouldLoad()) return
        this.processAspect(identifier, constructor)
        this.aspectRegistry[identifier] = constructor
        constructor.afterLoad()
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
                    return this.el.nxs_aspects.get(injectIdentifier)
                },
            })
        }
    }
    
    getAspects(el) {
        return el?.nxs_aspects
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
        if (node.hasAttribute(this.connectAttr) || node.tagName.toLowerCase().startsWith(config.customElementPrefix)) this.connectHostEl(node)
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
            new ElementEventHandler(el, descriptor)
        })
        el.nxs_handlers?.forEach(handler => {
            handler.connect()
        })
    }
    disconnectHandlerEl(el) {
        el.nxs_handlers?.forEach(handler => {
            handler.disconnect()
        })
        el.nxs_handlers = null
    }

    connectSubEl(el) {
        let identifiers = (el.getAttribute(this.connectAttr) ?? '')
            .split(' ').filter(identifier => identifier && identifier.includes('.'))
        identifiers.forEach(descriptor => {
            new ElementConnection(el, descriptor)
        })
        el.nxs_connections?.forEach(connection => {
            connection.connect()
        })
    }
    disconnectSubEl(el) {
        el.nxs_connections?.forEach(connection => {
            connection.disconnect()
        })
        el.nxs_connections = null
    }

    connectHostEl(el) {
        let identifiers = (el.getAttribute(this.connectAttr) ?? '')
                .split(' ').filter(identifier => identifier && !identifier.includes('.'))
        if (this.#customTags[el.tagName]) {
            identifiers.push(this.#customTags[el.tagName])
        }
        if (!identifiers.length) return
        identifiers.forEach(identifier => {
            this.injectAspect(el, identifier)
        })
        let scopeString = el.nxs_aspects.keys().reduce((acc, key) => acc + ` ${key} `, '')
        el.setAttribute(`${config.attributePrefix}scope`, scopeString)
        if (el.nxs_aspects && config.observeAspectAttributes) this.aspectObserver.observe(el, {attributes: true, attributeOldValue: true})
        el.nxs_aspects?.forEach(aspect => {
            aspect.connect()
        })
    }
    disconnectHostEl(el) {
        el.nxs_aspects?.forEach(aspect => {
            aspect.disconnect()
        })
    }

    injectAspect(el, identifier, attributes = {}) {
        if (!this.aspectRegistry[identifier]) {
            console.warn(`Aspect ${identifier} not found in register, skipping.`)
            return
        }
        if (el.nxs_aspects?.has(identifier)) return
        Object.entries(this.aspectRegistry[identifier].injectedAspects).forEach(([injectIdentifier, injectAttributes]) => {
            this.injectAspect(el, injectIdentifier, injectAttributes)
        })
        new this.aspectRegistry[identifier](el, this, attributes)
    }

    aspectObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (!mutation.attributeName.startsWith(config.attributePrefix)) return
            const el = mutation.target
            const newVal = el.getAttribute(mutation.attributeName)
            if (mutation.oldValue == newVal) return
            if(mutation.attributeName == config.attributePrefix + 'scope' && el.nxs_aspects) {
                let scopeString = el.nxs_aspects.keys().reduce((acc, key) => acc + ` ${key} `, '')
                if (newVal == scopeString) return
                el.setAttribute(config.attributePrefix + 'scope', scopeString)
                return
            }
            el.nxs_aspects?.forEach(aspect => {
                if (mutation.attributeName == `${config.attributePrefix}${aspect.identifier}-reconnect`) {
                    window.requestAnimationFrame(() => {
                        aspect.disconnect()
                        aspect.connect()
                        el.removeAttribute(`${config.attributePrefix}${aspect.identifier}-reconnect`)
                    })
                }
                aspect.constructor.attributeSyncer.attributeChanged(aspect, mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })
}

export default new App()