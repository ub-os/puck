import ElementEventHandler from "~/_jcores/ElementEventHandler"
import ElementConnection from "~/_jcores/ElementConnection"
import config from "~/_jcores/Config"

export default class Connector {
    constructor(registry) {
        this.registry = registry
    }

    orphanHosts = new Map()
    orphans = new Map()

    addOrphan(el, hostId, identifier) {
        if (!this.orphanHosts.has(hostId)) {
            this.orphanHosts.set(hostId, new Map())
        }
        if (!this.orphanHosts.get(hostId).has(el)) {
            this.orphanHosts.get(hostId).set(el, new Set())
        }
        this.orphanHosts.get(hostId).get(el).add(identifier)

        if (!this.orphans.has(el)) {
            this.orphans.set(el, new Set())
        }
        this.orphans.get(el).add(hostId)
    }
    removeOrphan(el, hostId = '', identifier = '') {
        if (!hostId) {
            this.orphans.get(el)?.forEach((hostId) => {
                this.orphanHosts.get(hostId)?.delete(el)
                if (!this.orphanHosts.get(hostId).size) {
                    this.orphanHosts.delete(hostId)
                }
            })
            this.orphans.delete(el)
            return
        }
        if (!this.orphanHosts.has(hostId)) return
        if (identifier) {
            this.orphanHosts.get(hostId).get(el)?.delete(identifier)
            if (!this.orphanHosts.get(hostId).get(el).size) {
                this.orphanHosts.get(hostId).delete(el)
                if (!this.orphanHosts.get(hostId).size) {
                    this.orphanHosts.delete(hostId)
                }
            }
        } else {
            this.orphanHosts.get(hostId).delete(el)
            if (!this.orphanHosts.get(hostId).size) {
                this.orphanHosts.delete(hostId)
            }
        }
        this.orphans.get(el)?.delete(hostId)
        if (!this.orphans.get(el).size) {
            this.orphans.delete(el)
        }
    }
    get connectAttr() {
        return config.attributePrefix + config.connectAttribute
    }
    get handlerAttr() {
        return config.attributePrefix + config.handlerAttribute
    }
    getConnectEls(el = document) {
        return el.querySelectorAll(`[${config.attributePrefix}${config.connectAttribute}]${this.registry.customTagSelector}`)
    }
    getHandlerEls(el = document) {
        return el.querySelectorAll(`[${config.attributePrefix}${config.handlerAttribute}]`)
    }
    connect() {
        this.connectNode(document.body)
        if (config.observeChildList) {
            this.childListObserver.observe(document.body, { childList: true, subtree: true })
        }
        if (config.observeAttributes) {
            this.attributeObserver.observe(document.body, { attributes: true, attributeOldValue: true, attributeFilter: [this.connectAttr, this.handlerAttr], subtree: true })
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
            const el = mutation.target
            if (mutation.attributeName === this.connectAttr) {
                const oldIds = mutation.oldValue.split(' ') ?? []
                const newIds = el.getAttribute(this.connectAttr).split(' ') ?? []
                const removed = oldIds.filter(id => !newIds.includes(id))
                const added = newIds.filter(id => !oldIds.includes(id))
                this.elementIdentifiersRemoved(el, this.filterElementIdentifiers(removed))
                this.hostIdentifiersRemoved(el, this.filterHostIdentifiers(removed))
                this.hostIdentifiersAdded(el, this.filterHostIdentifiers(added), true)
                this.elementIdentifiersAdded(el, this.filterElementIdentifiers(added))
                return
            }
            if (mutation.attributeName === this.handlerAttr) {
                this.handlerElRemoved(el)
                this.handlerElAdded(el)
                return
            }
        })
    })

    filterHostIdentifiers(identifiers) {
        return identifiers.filter(id => id && !id.includes('.'))
    }

    filterElementIdentifiers(identifiers) {
        return identifiers.filter(id => id.includes('.'))
    }

    connectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        for (let [selector, callback] of this.registry.connectedCallbackRegister.entries()) {
            if (node.matches(selector)) {
                callback(node)
            }
            node.querySelectorAll(selector).forEach(el => callback(el))
        }
        const connectNodes = this.getConnectEls(node)
        if (node.hasAttribute(this.connectAttr) || node.tagName.toLowerCase().startsWith(config.customElementPrefix)) this.hostElAdded(node)
        connectNodes.forEach(child => this.hostElAdded(child))
        if (node.hasAttribute(this.connectAttr)) this.childElAdded(node)
        connectNodes.forEach(child => this.childElAdded(child))
        if (node.hasAttribute(this.handlerAttr)) this.handlerElAdded(node)
        this.getHandlerEls(node).forEach(child => this.handlerElAdded(child))
    }
    disconnectNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) return
        if (node.hasAttribute(this.handlerAttr)) this.handlerElRemoved(node)
        this.getHandlerEls(node).forEach(child => this.handlerElRemoved(child))
        const connectNodes = this.getConnectEls(node)
        if (node.hasAttribute(this.connectAttr)) this.childElRemoved(node)
        connectNodes.forEach(child => this.childElRemoved(child))
        if (node.hasAttribute(this.connectAttr)) this.hostElRemoved(node)
        connectNodes.forEach(child => this.hostElRemoved(child))
    }

    childElAdded(el) {
        let identifiers = this.filterElementIdentifiers(el.getAttribute(this.connectAttr)?.split(' ') ?? [])
        this.elementIdentifiersAdded(el, identifiers)
    }

    childElRemoved(el) {
        el.nxs_connections?.forEach(connection => {
            connection.disconnect()
        })
        this.removeOrphan(el)
    }
    
    elementIdentifiersAdded(el, identifiers) {
        identifiers?.forEach(identifier => {
            if (el.nxs_connections?.get(identifier)?.connected) {
                el.nxs_connections.get(identifier).disconnect()
            }
            const connection = new ElementConnection(el, identifier)
            if (!connection.aspect && connection.hostId) {
                this.addOrphan(el, connection.hostId, identifier)
                return
            }
            connection.connect()
        })
    }

    elementIdentifiersRemoved(el, identifiers) {
        identifiers?.forEach(identifier => {
            const connection = el.nxs_connections?.get(identifier)
            connection?.disconnect()
            el.nxs_connections?.delete(identifier)
            if (connection.hostId) {
                this.removeOrphan(el, connection.hostId, identifier)
            }
        })
    }
    
    hostIdentifiersAdded(el, identifiers, connectDescendants = false) {
        if (!identifiers.length) return
        identifiers.forEach(identifier => {
            this.injectAspect(el, identifier)
        })
        this.setHostScopeAttribute(el)
        if (el.nxs_aspects && config.observeAspectAttributes) this.aspectObserver.observe(el, {attributes: true, attributeOldValue: true})
        el.nxs_aspects?.forEach(aspect => {
            if (!aspect || aspect.__internal.isConnected) return
            aspect.connected()
            aspect.__internal.isConnected = true
            if (el.id) {
                this.orphanHosts.get(el.id)?.forEach((identifiers, child) => {
                    this.elementIdentifiersAdded(child, [...identifiers].filter(id => id.includes(`${aspect.identifier}.`)))
                })
            }
            if (connectDescendants) {
                el.querySelectorAll(`[${this.connectAttr}*="${aspect.identifier}."]`).forEach(child => {
                    if (el !== child.closest(`[${config.attributePrefix}scope*=" ${aspect.identifier} "]`)) return
                    this.elementIdentifiersAdded(child,
                        child.getAttribute(this.connectAttr)
                            .split(' ')
                            .filter(id => id.includes(`${aspect.identifier}.`))
                    )
                })
            }
        })
    }

    hostIdentifiersRemoved(el, identifiers) {
        identifiers?.forEach(identifier => {
            const aspect = el.nxs_aspects?.get(identifier)
            this.disconnectAspect(aspect)
            el.nxs_aspects?.delete(identifier)
        })
        this.setHostScopeAttribute(el)
    }

    hostElAdded(el) {
        let identifiers = this.filterHostIdentifiers(el.getAttribute(this.connectAttr)?.split(' ') ?? [])
        if (this.registry.customTags[el.tagName]) {
            identifiers.push(this.registry.customTags[el.tagName])
        }
        this.hostIdentifiersAdded(el, identifiers)
    }


    hostElRemoved(el) {
        el.nxs_aspects?.forEach(aspect => {
            this.disconnectAspect(aspect)
        })
    }

    disconnectAspect(aspect) {
        aspect.__internal.elements.forEach((set, key) => {
            set.forEach(el => {
                el.nxs_connections.forEach((connection, identifier) => {
                    if (connection.aspect !== aspect) {
                        return
                    }
                    if (connection.hostId && connection.el.isConnected) {
                        this.addOrphan(el, connection.hostId, identifier)
                    }
                    connection.disconnect()
                    el.nxs_connections.delete(identifier)
                })
            })
        })
        aspect.disconnected()
        aspect.__internal.isConnected = false
    }

    injectAspect(el, identifier, attributes = {}) {
        if (!this.registry.aspectRegister.has(identifier)) {
            console.warn(`Aspect ${identifier} not found in register, skipping.`)
            return
        }
        if (el.nxs_aspects?.has(identifier)) return
        Object.entries(this.registry.aspectRegister.get(identifier).injectedAspects).forEach(([injectIdentifier, injectAttributes]) => {
            this.injectAspect(el, injectIdentifier, injectAttributes)
        })
        new (this.registry.aspectRegister.get(identifier))(el, this, attributes)
    }

    setHostScopeAttribute(el, oldVal = '') {
        let scopeString = el.nxs_aspects?.keys().reduce((acc, key) => acc + ` ${key} `, '') ?? ''
        if (oldVal == scopeString) return
        el.setAttribute(`${config.attributePrefix}scope`, scopeString)
    }

    aspectObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (!mutation.attributeName.startsWith(config.attributePrefix)) return
            const el = mutation.target
            const newVal = el.getAttribute(mutation.attributeName)
            if (mutation.oldValue == newVal) return
            if(mutation.attributeName == config.attributePrefix + 'scope' && el.nxs_aspects) {
                this.setHostScopeAttribute(el)
                return
            }
            el.nxs_aspects?.forEach(aspect => {
                if (mutation.attributeName == `${config.attributePrefix}${aspect.identifier}-reconnect`) {
                    window.requestAnimationFrame(() => {
                        aspect.disconnected()
                        aspect.connected()
                        el.removeAttribute(`${config.attributePrefix}${aspect.identifier}-reconnect`)
                    })
                }
                aspect.constructor.attributeSyncer.attributeChanged(aspect, mutation.attributeName, mutation.oldValue, newVal)
            })
        })
    })


    handlerElAdded(el) {
        el.getAttribute(this.handlerAttr).split(' ').forEach(descriptor => {
            new ElementEventHandler(el, descriptor)
        })
        el.nxs_handlers?.forEach(handler => {
            handler.connect()
        })
    }
    handlerElRemoved(el) {
        el.nxs_handlers?.forEach(handler => {
            handler.disconnect()
        })
        el.nxs_handlers = null
    }
}