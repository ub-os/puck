import { $, $$, $id, jsx } from "~/Utility/DomUtility"
import {camelCase, kebabCase} from "~/Utility/StringUtility"
import { ObserverCollector, MutationManager } from "~/Service/ObserverCollector"
import Controller from "~/Application/Controller"
import AttributeConverter from "~/Application/AttributeConverter";

class Application {
    #controllerIdx = 0
    #targetIdx = 0
    #actionIdx = 0
    controllerRegister = {}
    services = {
        observerCollector: ObserverCollector.inst,
    }
    register(identifier, constructor) {
        this.processController(identifier, constructor)
        constructor.registerCallback()
        this.controllerRegister[identifier] = constructor
    }
    processController(identifier, constructor) {
        constructor.writePropKey = {}
        constructor.readPropKey = {}
        constructor.identifier = identifier
        constructor.props = {
            ...Controller.props,
            ...constructor.props,
        }
        constructor.__attributeConverter = new AttributeConverter(constructor.props)
        constructor.writeProp = (propName, val) => constructor.__attributeConverter.write(propName, val)
        constructor.readProp = (propName, val) => constructor.__attributeConverter.read(propName, val)
        for (let prop in constructor.props) {
            constructor.writePropKey[prop] = `data-${identifier}:${kebabCase(prop)}`
            constructor.readPropKey[`data-${identifier}:${kebabCase(prop)}`] = prop
            Object.defineProperty(constructor.prototype, prop, {
                get() {
                    return this[`#${prop}`]
                },
                set(val) {
                    this.__setProp(prop, val, true)
                }
            })
        }
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
            Object.defineProperty(constructor.prototype, `${target}Target`, {
                get() {
                    return this[`${target}Targets`].values()?.next()?.value
                },
            })
        }
        for (let injectIdentifier of Object.keys(constructor.injects)) {
            Object.defineProperty(constructor.prototype, `${camelCase(injectIdentifier)}Controller`, {
                get() {
                    return this.el.controllerInstances.get(injectIdentifier)
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
    connect() {
        MutationManager.addById(
            'body-childList-observer',
            document.body,
            mutations => this.elementConnectionHandler(mutations),
            { childList: true, subtree: true },
        )
        this.controllerElements.forEach(el => this.connectControllerElement(el))
        this.targetElements.forEach(el => this.connectTargetElement(el))
        this.actionElements.forEach(el => this.connectActionElement(el))
    }

    disconnect() {
        MutationManager.remove('body-childList-observer')
        this.controllerElements.forEach(el => this.disconnectControllerElement(el))
        this.targetElements.forEach(el => this.disconnectTargetElement(el))
        this.actionElements.forEach(el => this.disconnectActionElement(el))
    }

    elementConnectionHandler(mutations) {
        mutations.forEach(mutation => {
            mutation.removedNodes.forEach(node => {
                if (node.nodeType !== Node.ELEMENT_NODE) return
                if (node.hasAttribute('data-action')) this.disconnectActionElement(node)
                node.$$('[data-action]').forEach(child => this.disconnectActionElement(child))
                if (node.hasAttribute('data-target')) this.disconnectTargetElement(node)
                node.$$('[data-target]').forEach(child => this.disconnectTargetElement(child))
                if (node.hasAttribute('data-controller')) this.disconnectControllerElement(node)
                node.$$('[data-controller]').forEach(child => this.disconnectControllerElement(child))
            })
            mutation.addedNodes.forEach(node => {
                if (node.nodeType !== Node.ELEMENT_NODE) return
                if (node.hasAttribute('data-controller')) this.connectControllerElement(node)
                node.$$('[data-controller]').forEach(child => this.connectControllerElement(child))
                if (node.hasAttribute('data-target')) this.connectTargetElement(node)
                node.$$('[data-target]').forEach(child => this.connectTargetElement(child))
                if (node.hasAttribute('data-action')) this.connectActionElement(node)
                node.$$('[data-action]').forEach(child => this.connectActionElement(child))
            })
        })
    }

    connectActionElement(el) {
        if (!el.id) {
            el.id = `_action${this.#actionIdx++}`
        }
        //console.log(`connecting action on #${el.id}`)
        el.dataset.action.split(' ').forEach(descriptor => {
            const [
                event,
                method,
                identifier,
                id
            ] = descriptor.split(/->|@|#/);
            const controllerElement = id ? $id(id) : el.closest(`[data-controller]`)
            const controller = controllerElement?.controllerInstances?.get(identifier)
            if (!method || !controller || typeof controller[method] !== 'function') return
            if (el.actionSettings?.[identifier]) {
                console.log(`Action ${identifier} already connected to ${el.id}`)
                return
            }
            !el.actionSettings ? el.actionSettings = {} : null
            el.actionSettings[identifier] = {
                event,
                listenerOptions: {},
            }
            ;['capture', 'once', 'passive'].forEach(opt => {
                const value = el.dataset[`${identifier}:event:${opt}`]
                el.actionSettings[identifier].listenerOptions[opt] = value === undefined || value === null || value === 'false' || value === '0' ? false : true
            })
            const attributeConverter = new AttributeConverter(controller.constructor.actions?.[method] || {})
            el.actionSettings[identifier].listener = e => {
                if (!controllerElement.controllersAreConnected) return
                el.hasAttribute(`data-${identifier}:event:prevent`) ? e.preventDefault() : null
                el.hasAttribute(`data-${identifier}:event:stop`) ? e.stopPropagation() : null
                e.actionElement = el
                const params = attributeConverter.props
                Object.entries({...el.dataset}).forEach(([key, value]) => {
                    if (key.startsWith(`${identifier}:param:`)) {
                        const propName = camelCase(key.replace(`${identifier}:param:`, ''))
                        params[propName] = attributeConverter.read(propName, value)
                    }
                })
                controller[method](e, params)
            }
            el.addEventListener(event, el.actionSettings[identifier].listener, el.actionSettings[identifier].listenerOptions)
        })
        this.actionObserver.observe(el, { attributes: true, attributeOldValue: true })
    }

    disconnectActionElement(el) {
        //console.log(`disconnecting action on #${el.id}`)
        if (!el.actionSettings) return
        Object.entries(el.actionSettings).forEach(([identifier, settings]) => {
            el.removeEventListener(settings.event,settings.listener, settings.listenerOptions);
        })
        delete el.actionSettings
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
            Object.entries(target.actionSettings).forEach(([identifier, settings]) => {
                target.removeEventListener(settings.event, settings.listener, settings.listenerOptions)
                ;['capture', 'once', 'passive'].forEach(opt => {
                    const value = target.dataset[`${identifier}:event:${opt}`]
                    settings.listenerOptions[opt] = value === undefined || value === null || value === 'false' || value === '0' ? false : true
                })
                target.addEventListener(settings.event, settings.listener, settings.listenerOptions)
            })
        }
    )

    connectTargetElement(el) {
        if (!el.id) {
            el.id = `_target${this.#targetIdx++}`
        }
        this.getTargetData(el).forEach(({ name, controller }) => {
            //console.log(`connecting target on #${controller.el.id}`)
            controller[`${name}Targets`].set(el.id, el)
            if (typeof controller[`${name}Connected`] == 'function') {
                controller[`${name}Connected`](el)
            }
        })
    }
    disconnectTargetElement(el) {
        this.getTargetData(el).forEach(({ name, controller }) => {
            controller[`${name}Targets`].delete(el.id)
            if (typeof controller[`${name}Disconnected`] == 'function') {
                controller[`${name}Disconnected`](el)
            }
        })
    }
    getTargetData(el) {
        return el.dataset.target.split(' ').reduce((result, descriptor) => {
            const [
                name,
                identifier,
                id
            ] = descriptor.split(/@|#/);
            if (!name || !identifier ) return
            const controllerElement = id ? $id(id) : el.closest(`[data-controller]`)
            const controller = controllerElement?.controllerInstances?.get(identifier)
            if (controller) result.push({ name, controller })
            return result
        }, [])
    }

    connectControllerElement(el) {
        if (!el.controllerInstances) {
            el.controllerInstances = new Map()
            if (!el.id) {
                el.id = `_controller${this.#controllerIdx++}`
            }
            el.dataset.controller?.split(' ').forEach(identifier => {
                this.injectController(el, identifier)
            })
            this.controllerObserver.observe(el, { attributes: true, attributeOldValue: true })
        }
        el.controllersAreConnected = true
        //console.log(`connecting '${el.dataset.controller}' ond #${el.id}`)
        el.controllerInstances.forEach(controller => {
            controller.connect()
        })
    }
    disconnectControllerElement(el) {
        //console.log(`disconnecting '${el.dataset.controller}' ond #${el.id}`)
        el.controllersAreConnected = false
        el.controllerInstances?.forEach(controller => {
            controller.disconnect()
        })
        delete el.controllerInstances
    }

    injectController(el, identifier, props = {}) {
        if (!this.controllerRegister[identifier]) {
            console.warn(`Controller ${identifier} not found in register, skipping.`)
            return
        }
        if (el.controllerInstances.has(identifier)) {
            console.warn(`Controller ${identifier} already used on this element, overriding.`)
        }
        Object.entries(this.controllerRegister[identifier].injects).forEach(([injectIdentifier, injectProps]) => {
            this.injectController(el, injectIdentifier, injectProps)
        })
        el.controllerInstances.set(identifier, new this.controllerRegister[identifier](el, props))
    }

    controllerObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.attributeName === 'data-controller') {
                this.disconnectControllerElement(mutation.target)
                this.connectControllerElement(mutation.target)
                return
            }
            if (!mutation.attributeName.startsWith(`data-`)) return
            const newVal = mutation.target.getAttribute(mutation.attributeName)
            mutation.target.controllerInstances?.forEach(controller => {
                controller.__attributeChanged(mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })

    reset() {
        this.services.observerCollector?.reset()
        this.disconnect()
    }
}

const App = new Application()
export default App