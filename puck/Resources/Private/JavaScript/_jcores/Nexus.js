import { camelCase, kebabCase } from "./StringUtility"
import CoreAttributeSyncer from "./CoreAttributeSyncer"
import Core from "./Core"
import ElementUnit from "./ElementUnit"
import Trigger from "./Trigger"
import EventTrigger from "./EventTrigger"
import config from "./Config"

class Nexus {
    #idx = 0
    coreRegistry = {}
    eventRegistry = {}
    connectedCallbackRegistry = {}
    #coreCustomElementSelector = ''
    #customElementTags = []

    config = config

    get coreAttribute() {
        return this.config.attributePrefix + this.config.coreAttribute
    }
    get coreElAttribute() {
        return this.config.attributePrefix + this.config.coreElAttribute
    }
    get triggerAttribute() {
        return this.config.attributePrefix + this.config.triggerAttribute
    }
    get eventTriggerAttribute() {
        return this.config.attributePrefix + this.config.eventTriggerAttribute
    }
    get coreSelector() {
        return `[${this.config.attributePrefix}${this.config.coreAttribute}]${this.#coreCustomElementSelector}`
    }
    get coreElSelector() {
        return `[${this.config.attributePrefix}${this.config.coreElAttribute}]`
    }
    get triggerSelector() {
        return `[${this.config.attributePrefix}${this.config.triggerAttribute}]`
    }
    get eventTriggerSelector() {
        return `[${this.config.attributePrefix}${this.config.eventTriggerAttribute}]`
    }

    registerCore(identifier, constructor) {
        if (typeof identifier == 'object') {
            Object.entries(identifier).forEach(([key, value]) => {
                this.registerCore(kebabCase(key), value)
            })
            return
        }
        this.processCore(identifier, constructor)
        constructor.registerCallback()
        this.coreRegistry[identifier] = constructor
    }

    registerCoreCustomElement(identifier) {
        if (Array.isArray(identifier)) {
            identifier.forEach(value => {
                this.registerCoreCustomElement(value)
            })
            return
        }
        if (!this.coreRegistry[identifier]) {
            console.warn(`Core ${identifier} not found in register, skipping custom element registration.`)
            return
        }
        this.#coreCustomElementSelector += `, ${this.config.coreCustomElementPrefix}${identifier}`
        this.#customElementTags[(this.config.coreCustomElementPrefix + identifier).toUpperCase()] = identifier
        customElements.define(this.config.coreCustomElementPrefix + identifier, class extends HTMLElement {
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
    processCore(identifier, constructor) {
        constructor.identifier = identifier
        constructor.attributes = {
            ...Core.attributes,
            ...constructor.attributes,
        }
        constructor.attributeSyncer = new CoreAttributeSyncer(identifier, constructor, constructor.attributes)
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
        if (Array.isArray(constructor.elements)) {
            constructor.elements = convertToObj(constructor.elements)
        }
        if (Array.isArray(constructor.injects)) {
            constructor.injects = convertToObj(constructor.injects)
        }
        for (let elementName of Object.keys(constructor.elements)) {
            Object.defineProperty(constructor.prototype, `${elementName}Elements`, {
                get() {
                    if (!this[`#${elementName}Elements`]) {
                        this[`#${elementName}Elements`] = new Map()
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
        for (let injectIdentifier of Object.keys(constructor.injects)) {
            Object.defineProperty(constructor.prototype, `${camelCase(injectIdentifier)}Core`, {
                get() {
                    return this.el.jc_cores.get(injectIdentifier)
                },
            })
        }
    }

    get coreElements() {
        return document.querySelectorAll(this.coreSelector)
    }
    get elementUnitElements() {
        return document.querySelectorAll(this.coreElSelector)
    }
    get triggerElements() {
        return document.querySelectorAll(this.triggerSelector)
    }
    get eventTriggerElements() {
        return document.querySelectorAll(this.eventTriggerSelector)
    }

    getCores(el) {
        return el?.jc_cores || null
    }

    getCore(el, coreName) {
        return el?.get(coreName) || null
    }

    connect() {
        this.connectNode(document.body)
        if (this.config.observeDom) {
            this.domObserver.observe(document.body, { childList: true, subtree: true })
        }
    }

    disconnect() {
        this.domObserver.takeRecords()
        this.domObserver.disconnect()
        this.coreObserver.disconnect()
        this.triggerObserver.disconnect()
        this.disconnectNode(document.body)
        this.#idx = 0
    }

    domObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.removedNodes.forEach(node => {
                this.disconnectNode(node)
            })
            mutation.addedNodes.forEach(node => {
                this.connectNode(node)
            })
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
        if (node.hasAttribute(this.coreAttribute) || node.tagName.startsWith('CORE-')) this.connectCoreElement(node)
        node.querySelectorAll(this.coreSelector).forEach(child => this.connectCoreElement(child))
        if (node.hasAttribute(this.coreElAttribute)) this.connectElementUnitElement(node)
        node.querySelectorAll(this.coreElSelector).forEach(child => this.connectElementUnitElement(child))
        if (node.hasAttribute(this.triggerAttribute)) this.connectTriggerElement(node)
        node.querySelectorAll(this.triggerSelector).forEach(child => this.connectTriggerElement(child))
        if (node.hasAttribute(this.eventTriggerAttribute)) this.connectTriggerElement(node, true)
        node.querySelectorAll(this.eventTriggerSelector).forEach(child => this.connectTriggerElement(child, true))
    }

    disconnectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        if (node.hasAttribute(this.eventTriggerAttribute)) this.disconnectTriggerElement(node)
        node.querySelectorAll(this.eventTriggerSelector).forEach(child => this.disconnectTriggerElement(child))
        if (node.hasAttribute(this.triggerAttribute)) this.disconnectTriggerElement(node)
        node.querySelectorAll(this.triggerSelector).forEach(child => this.disconnectTriggerElement(child))
        if (node.hasAttribute(this.coreElAttribute)) this.disconnectElementUnitElement(node)
        node.querySelectorAll(this.coreElSelector).forEach(child => this.disconnectElementUnitElement(child))
        if (node.hasAttribute(this.coreAttribute)) this.disconnectCoreElement(node)
        node.querySelectorAll(this.coreSelector).forEach(child => this.disconnectCoreElement(child))
    }

    connectTriggerElement(el, isEventTrigger = false) {
        if (!el.id) {
            el.id = `jc-el--${this.#idx++}`
        }
        el.getAttribute(isEventTrigger ? this.eventTriggerAttribute : this.triggerAttribute).split(' ').forEach(descriptor => {
            if (isEventTrigger) {
                new EventTrigger(el, descriptor, this.eventRegistry)
            } else {
                new Trigger(el, descriptor)
            }
        })
        if (!el.jc_triggers) return
        el.jc_triggers.forEach(trigger => {
            trigger.connect()
        })
        this.triggerObserver.observe(el, { attributes: true, attributeOldValue: true })
    }

    disconnectTriggerElement(el) {
        el.jc_triggers?.forEach(trigger => {
            trigger.disconnect()
        })
        el.jc_triggers = null
    }

    triggerObserver = new MutationObserver(
        mutations => {
            const target = mutations[0].target
            let hasEventModifier = false
            mutations.forEach(mutation => {
                if (mutation.attributeName.includes(`.event.`)) hasEventModifier = true
                if (mutation.attributeName === this.triggerAttribute || mutation.attributeName === this.eventTriggerAttribute) {
                    this.disconnectTriggerElement(target)
                    this.connectTriggerElement(target)
                }
            })
            if (!hasEventModifier) return
            target['jc_triggers']?.forEach(trigger => {
                trigger.disconnect()
                trigger.setListenerOptions()
                trigger.connect()
            })
        }
    )

    connectElementUnitElement(el) {
        if (!el.id) {
            el.id = `jc-el--${this.#idx++}`
        }
        el.getAttribute(this.coreElAttribute).split(' ').forEach(descriptor => {
            new ElementUnit(el, descriptor)
        })
        if (!el.jc_elementUnits) return
        el.jc_elementUnits.forEach(element => {
            element.connect()
        })
    }

    disconnectElementUnitElement(el) {
        el.jc_elementUnits?.forEach(element => {
            element.disconnect()
        })
        el.jc_elementUnits = null
    }

    connectCoreElement(el) {
        if (!el.jc_cores) {
            if (!el.id) {
                el.id = `jc-el--${this.#idx++}`
            }
            let coreIdentifiers = el.getAttribute(this.coreAttribute) ?? ''
            if (this.#customElementTags[el.tagName]) {
                coreIdentifiers = coreIdentifiers + ' ' + this.#customElementTags[el.tagName]
            }
            coreIdentifiers.split(' ').forEach(identifier => {
                if (!identifier) return
                this.injectCore(el, identifier)
            })
            this.coreObserver.observe(el, { attributes: true, attributeOldValue: true })
        }
        if (!el.jc_cores) return
        el.jc_cores?.forEach(core => {
            if (core.__connected || core.asleep) return
            core.connect()
            core.__connected = true
        })
    }
    disconnectCoreElement(el) {
        el.jc_cores?.forEach(core => {
            if (!core.__connected || core.asleep) return
            core.disconnect()
            core.__connected = false
        })
        el.jc_cores = null
    }

    injectCore(el, identifier, attributes = {}) {
        if (!this.coreRegistry[identifier]) {
            console.warn(`Core ${identifier} not found in register, skipping.`)
            return
        }
        if (el.jc_cores?.has(identifier)) {
            console.warn(`Core ${identifier} already used on this element, overriding.`)
        }
        Object.entries(this.coreRegistry[identifier].injects).forEach(([injectIdentifier, injectAttributes]) => {
            this.injectCore(el, injectIdentifier, injectAttributes)
        })
        new this.coreRegistry[identifier](el, attributes)
    }

    coreObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (!mutation.attributeName.startsWith(this.config.attributePrefix)) return
            if (mutation.attributeName === this.coreAttribute) {
                this.disconnectCoreElement(mutation.target)
                this.connectCoreElement(mutation.target)
                return
            }
            const newVal = mutation.target.getAttribute(mutation.attributeName)
            mutation.target.jc_cores?.forEach(core => {
                if (mutation.attributeName == `${this.config.attributePrefix}${core.identifier}-reconnect`) {
                    window.requestAnimationFrame(() => {
                        core.disconnect()
                        core.connect()
                        mutation.target.removeAttribute(`${this.config.attributePrefix}${core.identifier}-reconnect`)
                    })
                }
                core.constructor.attributeSyncer.attributeChanged(core, mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })
}

export default new Nexus()