import { camelCase, kebabCase } from "./Utility/StringUtility"
import { $$ } from "./Utility/DomUtility"
import AttributeSyncer from "./Service/AttributeSyncer"
import Controller from "./Controller"
import Action from "./Action"
import Target from "./Target"
import StimEvent from "./Event"

class Application {
    #idx = 0
    controllerRegistry = {}
    eventRegistry = {}
    connectedCallbackRegistry = {}
    registerController(identifier, constructor) {
        if (typeof identifier == 'object') {
            Object.entries(identifier).forEach(([key, value]) => {
                this.registerController(kebabCase(key), value)
            })
            return
        }
        this.processController(identifier, constructor)
        constructor.registerCallback()
        this.controllerRegistry[identifier] = constructor
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
    processController(identifier, constructor) {
        constructor.identifier = identifier
        constructor.attributes = {
            ...Controller.attributes,
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
        if (Array.isArray(constructor.targets)) {
            constructor.targets = convertToObj(constructor.targets)
        }
        if (Array.isArray(constructor.injects)) {
            constructor.injects = convertToObj(constructor.injects)
        }
        if (Array.isArray(constructor.actions)) {
            constructor.actions = convertToObj(constructor.actions)
        }
        for (let target of Object.keys(constructor.targets)) {
            const map = new Map()
            Object.defineProperty(constructor.prototype, `${target}Targets`, {
                get() {
                    return map
                }
            })
            Object.defineProperty(constructor.prototype, `${target}Target`, {
                get() {
                    return this[`${target}Targets`].values()?.next()?.value
                },
            })
        }
        for (let injectIdentifier of Object.keys(constructor.injects)) {
            Object.defineProperty(constructor.prototype, `${camelCase(injectIdentifier)}Controller`, {
                get() {
                    return this.el.stimControllers.get(injectIdentifier)
                },
            })
        }
    }

    get controllerElements() {
        return $$('[data-controller]')
    }
    get targetElements() {
        return $$('[data-target]')
    }
    get actionElements() {
        return $$('[data-action]')
    }
    get eventElements() {
        return $$('[data-event]')
    }
    connect() {
        this.connectNode(document.body)
        this.domObserver.observe(document.body, { childList: true, subtree: true })
    }

    disconnect() {
        this.domObserver.takeRecords()
        this.domObserver.disconnect()
        this.controllerObserver.disconnect()
        this.actionObserver.disconnect()
        this.eventObserver.disconnect()
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
        if (node.hasAttribute('data-controller')) this.connectControllerElement(node)
        node.$$('[data-controller]').forEach(child => this.connectControllerElement(child))
        if (node.hasAttribute('data-target')) this.connectTargetElement(node)
        node.$$('[data-target]').forEach(child => this.connectTargetElement(child))
        if (node.hasAttribute('data-action')) this.connectActionElement(node)
        node.$$('[data-action]').forEach(child => this.connectActionElement(child))
        if (node.hasAttribute('data-event')) this.connectEventElement(node)
        node.$$('[data-event]').forEach(child => this.connectEventElement(child))
    }

    disconnectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        if (node.hasAttribute('data-event')) this.disconnectEventElement(node)
        node.$$('[data-event]').forEach(child => this.disconnectEventElement(child))
        if (node.hasAttribute('data-action')) this.disconnectActionElement(node)
        node.$$('[data-action]').forEach(child => this.disconnectActionElement(child))
        if (node.hasAttribute('data-target')) this.disconnectTargetElement(node)
        node.$$('[data-target]').forEach(child => this.disconnectTargetElement(child))
        if (node.hasAttribute('data-controller')) this.disconnectControllerElement(node)
        node.$$('[data-controller]').forEach(child => this.disconnectControllerElement(child))
    }

    connectActionElement(el) {
        if (!el.id) {
            el.id = `stim-el-${this.#idx++}`
        }
        el.dataset.action.split(' ').forEach(descriptor => {
            new Action(el, descriptor)
        })
        if (!el.stimActions) return
        el.stimActions.forEach(action => {
            action.connect()
        })
        this.actionObserver.observe(el, { attributes: true, attributeOldValue: true })
    }

    disconnectActionElement(el) {
        el.stimActions?.forEach(action => {
            action.disconnect()
        })
        el.stimActions = null
    }

    actionObserver = new MutationObserver(
        mutations => {
            const target = mutations[0].target
            let hasEventModifier = false
            mutations.forEach(mutation => {
                if (mutation.attributeName.includes(`:event:`)) hasEventModifier = true
                if (mutation.attributeName === `data-action`) {
                    this.disconnectActionElement(target)
                    this.connectActionElement(target)
                }
            })
            if (!hasEventModifier) return
            target['stimActions']?.forEach(action => {
                action.disconnect()
                action.setListenerOptions()
                action.connect()
            })
        }
    )

    connectEventElement(el) {
        if (!el.id) {
            el.id = `stim-el-${this.#idx++}`
        }
        el.dataset.event.split(' ').forEach(descriptor => {
            new StimEvent(el, descriptor, this.eventRegistry)
        })
        if (!el.stimEvents) return
        el.stimEvents.forEach(event => {
            event.connect()
        })
        this.eventObserver.observe(el, { attributes: true, attributeOldValue: true })
    }

    disconnectEventElement(el) {
        el.stimEvents?.forEach(event => {
            event.disconnect()
        })
        el.stimEvents = null
    }

    eventObserver = new MutationObserver(
        mutations => {
            const target = mutations[0].target
            let hasEventModifier = false
            mutations.forEach(mutation => {
                if (mutation.attributeName.includes(`:event:`)) hasEventModifier = true
                if (mutation.attributeName === `data-event`) {
                    this.disconnectEventElement(target)
                    this.connectEventElement(target)
                }
            })
            if (!hasEventModifier) return
            target['stimEvents']?.forEach(event => {
                event.disconnect()
                event.setListenerOptions()
                event.connect()
            })
        }
    )

    connectTargetElement(el) {
        if (!el.id) {
            el.id = `stim-el-${this.#idx++}`
        }
        el.dataset.target.split(' ').forEach(descriptor => {
            new Target(el, descriptor)
        })
        if (!el.stimTargets) return
        el.stimTargets.forEach(action => {
            action.connect()
        })
    }

    disconnectTargetElement(el) {
        el.stimTargets?.forEach(target => {
            target.disconnect()
        })
        el.stimTargets = null
    }

    connectControllerElement(el) {
        if (!el.stimControllers) {
            if (!el.id) {
                el.id = `stim-el-${this.#idx++}`
            }
            el.dataset.controller?.split(' ').forEach(identifier => {
                this.injectController(el, identifier)
            })
            this.controllerObserver.observe(el, { attributes: true, attributeOldValue: true })
        }
        if (!el.stimControllers) return
        el.stimControllers?.forEach(controller => {
            if (controller.__connected || controller.asleep) return
            controller.connect()
            controller.__connected = true
        })
    }
    disconnectControllerElement(el) {
        el.stimControllers?.forEach(controller => {
            if (!controller.__connected || controller.asleep) return
            controller.disconnect()
            controller.__connected = false
        })
        el.stimControllers = null
    }

    injectController(el, identifier, attributes = {}) {
        if (!this.controllerRegistry[identifier]) {
            console.warn(`Controller ${identifier} not found in register, skipping.`)
            return
        }
        if (el.stimControllers?.has(identifier)) {
            console.warn(`Controller ${identifier} already used on this element, overriding.`)
        }
        Object.entries(this.controllerRegistry[identifier].injects).forEach(([injectIdentifier, injectAttributes]) => {
            this.injectController(el, injectIdentifier, injectAttributes)
        })
        new this.controllerRegistry[identifier](el, attributes)
    }

    controllerObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (!mutation.attributeName.startsWith(`data-`)) return
            if (mutation.attributeName === 'data-controller') {
                this.disconnectControllerElement(mutation.target)
                this.connectControllerElement(mutation.target)
                return
            }
            const newVal = mutation.target.getAttribute(mutation.attributeName)
            mutation.target.stimControllers?.forEach(controller => {
                if (mutation.attributeName == `data-${controller.identifier}-reconnect`) {
                    window.requestAnimationFrame(() => {
                        controller.disconnect()
                        controller.connect()
                        mutation.target.removeAttribute(`data-${controller.identifier}-reconnect`)
                    })
                }
                controller.constructor.attributeSyncer.attributeChanged(controller, mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })
}

const App = new Application()
export default App