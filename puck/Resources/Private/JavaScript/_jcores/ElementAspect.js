export default class ElementAspect {
    static attributes = {}
    static connectedElements = []
    static injectedAspects = []
    static afterLoad() {}
    static shouldLoad() { return true }

    __internal
    get el() { return this.__internal.host }
    get element() { return this.__internal.host }
    get app() { return this.__internal.app }
    get identifier() { return this.constructor.identifier }
    constructor(host, app, attributes = {}) {
        this.__internal = {
            host,
            app,
            elements: new Map(this.constructor.connectedElements.map(name => [name, new Set()]))
        }
        this.constructor.attributeSyncer.initializeAttributes(this, attributes)
        !host.nxs_aspects ? host.nxs_aspects = new Map() : null
        host.nxs_aspects.set(this.identifier, this)
        this.initialized()
    }

    dispatch(type, {
        target = this.el,
        detail = {},
        prefix = this.identifier,
        bubbles = true,
        cancelable = true
    } = {}) {
        type = prefix ? `${prefix}:${type}` : type
        const event = new CustomEvent(type, { detail, bubbles, cancelable })
        target.dispatchEvent(event)
        return event
    }

    initialized()  { return this }
    connected() { return this }
    disconnected() { return this }
    attributeChanged(name, oldValue, newValue) { }
}