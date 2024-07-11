import { camelCase, kebabCase } from "./Utility/StringUtility"
import { $$ } from "./Utility/DomUtility"
import AttributeSyncer from "./Service/AttributeSyncer"
import Core from "./Core"
import Unit from "./Unit"
import Trigger from "./Trigger"

class Nexus {
    #idx = 0
    coreRegistry = {}
    eventRegistry = {}
    connectedCallbackRegistry = {}
    #attributePrefix = 'data-'
    #secondaryAttributePrefix = ''
    #coreCssSelector = '[data-core]'
    #customElementTags = []
    get attributePrefix() { return this.#attributePrefix }
    set attributePrefix(value) { this.#attributePrefix = value }
    get secondaryAttributePrefix() {
        if (this.#secondaryAttributePrefix) return this.#secondaryAttributePrefix
        return this.#attributePrefix
    }
    set secondaryAttributePrefix(value) { this.#secondaryAttributePrefix = value }
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
        if (!this.coreRegistry[identifier]) {
            console.warn(`Core ${identifier} not found in register, skipping custom element registration.`)
            return
        }
        this.#coreCssSelector += `, core-${identifier}`
        this.#customElementTags[`CORE-${identifier.toUpperCase()}`] = identifier
        customElements.define(`core-${identifier}`, class extends HTMLElement {
            constructor() {
                super()
            }
            _jcoresAttrPrefix = ''
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
        constructor.attributeSyncer = new AttributeSyncer(identifier, constructor, constructor.attributes)
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
        if (Array.isArray(constructor.units)) {
            constructor.units = convertToObj(constructor.units)
        }
        if (Array.isArray(constructor.injects)) {
            constructor.injects = convertToObj(constructor.injects)
        }
        if (Array.isArray(constructor.triggerables)) {
            constructor.triggerables = convertToObj(constructor.triggerables)
        }
        for (let unit of Object.keys(constructor.units)) {
            Object.defineProperty(constructor.prototype, `${unit}Units`, {
                get() {
                    if (!this[`#${unit}Units`]) {
                        this[`#${unit}Units`] = new Map()
                    }
                    return this[`#${unit}Units`]
                }
            })
            Object.defineProperty(constructor.prototype, `${unit}Unit`, {
                get() {
                    return this[`${unit}Units`].values()?.next()?.value
                },
            })
        }
        for (let injectIdentifier of Object.keys(constructor.injects)) {
            Object.defineProperty(constructor.prototype, `${camelCase(injectIdentifier)}Core`, {
                get() {
                    return this.el._jcCores.get(injectIdentifier)
                },
            })
        }
    }

    get coreElements() {
        return $$(this.#coreCssSelector)
    }
    get unitElements() {
        return $$('[data-unit]')
    }
    get triggerElements() {
        return $$('[data-trigger]')
    }
    connect() {
        this.connectNode(document.body)
        this.domObserver.observe(document.body, { childList: true, subtree: true })
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
            node.$$(selector).forEach(el => callback(el))
        }
        if (node.hasAttribute('data-core') || node.tagName.startsWith('CORE-')) this.connectCoreElement(node)
        node.$$(this.#coreCssSelector).forEach(child => this.connectCoreElement(child))
        if (node.hasAttribute('data-unit')) this.connectUnitElement(node)
        node.$$('[data-unit]').forEach(child => this.connectUnitElement(child))
        if (node.hasAttribute('data-trigger')) this.connectTriggerElement(node)
        node.$$('[data-trigger]').forEach(child => this.connectTriggerElement(child))
    }

    disconnectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        if (node.hasAttribute('data-trigger')) this.disconnectTriggerElement(node)
        node.$$('[data-trigger]').forEach(child => this.disconnectTriggerElement(child))
        if (node.hasAttribute('data-unit')) this.disconnectUnitElement(node)
        node.$$('[data-unit]').forEach(child => this.disconnectUnitElement(child))
        if (node.hasAttribute('data-core')) this.disconnectCoreElement(node)
        node.$$(this.#coreCssSelector).forEach(child => this.disconnectCoreElement(child))
    }

    connectTriggerElement(el) {
        if (!el.id) {
            el.id = `_jc-el-${this.#idx++}`
        }
        el.getAttribute('data-trigger').split(' ').forEach(descriptor => {
            new Trigger(el, descriptor)
        })
        if (!el._jcTriggers) return
        el._jcTriggers.forEach(trigger => {
            trigger.connect()
        })
        this.triggerObserver.observe(el, { attributes: true, attributeOldValue: true })
    }

    disconnectTriggerElement(el) {
        el._jcTriggers?.forEach(trigger => {
            trigger.disconnect()
        })
        el._jcTriggers = null
    }

    triggerObserver = new MutationObserver(
        mutations => {
            const target = mutations[0].target
            let hasEventModifier = false
            mutations.forEach(mutation => {
                if (mutation.attributeName.includes(`:event:`)) hasEventModifier = true
                if (mutation.attributeName === `data-trigger`) {
                    this.disconnectTriggerElement(target)
                    this.connectTriggerElement(target)
                }
            })
            if (!hasEventModifier) return
            target['_jcTriggers']?.forEach(trigger => {
                trigger.disconnect()
                trigger.setListenerOptions()
                trigger.connect()
            })
        }
    )

    connectUnitElement(el) {
        if (!el.id) {
            el.id = `_jc-el-${this.#idx++}`
        }
        el.getAttribute('data-unit').split(' ').forEach(descriptor => {
            new Unit(el, descriptor)
        })
        if (!el._jcUnits) return
        el._jcUnits.forEach(unit => {
            unit.connect()
        })
    }

    disconnectUnitElement(el) {
        el._jcUnits?.forEach(unit => {
            unit.disconnect()
        })
        el._jcUnits = null
    }

    connectCoreElement(el) {
        if (!el._jcCores) {
            if (!el.id) {
                el.id = `_jc-el-${this.#idx++}`
            }
            let coreIdentifiers = el.getAttribute('data-core') ?? ''
            if (this.#customElementTags[el.tagName]) {
                coreIdentifiers = coreIdentifiers + ' ' + this.#customElementTags[el.tagName]
            }
            coreIdentifiers.split(' ').forEach(identifier => {
                this.injectCore(el, identifier)
            })
            this.coreObserver.observe(el, { attributes: true, attributeOldValue: true })
        }
        if (!el._jcCores) return
        el._jcCores?.forEach(core => {
            if (core.__connected || core.asleep) return
            core.connect()
            core.__connected = true
        })
    }
    disconnectCoreElement(el) {
        el._jcCores?.forEach(core => {
            if (!core.__connected || core.asleep) return
            core.disconnect()
            core.__connected = false
        })
        el._jcCores = null
    }

    injectCore(el, identifier, attributes = {}) {
        if (!this.coreRegistry[identifier]) {
            console.warn(`Core ${identifier} not found in register, skipping.`)
            return
        }
        if (el._jcCores?.has(identifier)) {
            console.warn(`Core ${identifier} already used on this element, overriding.`)
        }
        Object.entries(this.coreRegistry[identifier].injects).forEach(([injectIdentifier, injectAttributes]) => {
            this.injectCore(el, injectIdentifier, injectAttributes)
        })
        new this.coreRegistry[identifier](el, attributes)
    }

    coreObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (!mutation.attributeName.startsWith(`data-`)) return
            if (mutation.attributeName === 'data-core') {
                this.disconnectCoreElement(mutation.target)
                this.connectCoreElement(mutation.target)
                return
            }
            const newVal = mutation.target.getAttribute(mutation.attributeName)
            mutation.target._jcCores?.forEach(core => {
                if (mutation.attributeName == `data-${core.identifier}-reconnect`) {
                    window.requestAnimationFrame(() => {
                        core.disconnect()
                        core.connect()
                        mutation.target.removeAttribute(`data-${core.identifier}-reconnect`)
                    })
                }
                core.constructor.attributeSyncer.attributeChanged(core, mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })
}

export default new Nexus()