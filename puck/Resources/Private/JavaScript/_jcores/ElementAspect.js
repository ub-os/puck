export default class ElementAspect {
    static attributes = {}
    static connectedElements = []
    static injectedAspects = []
    static afterLoad() {}
    static shouldLoad() { return true }

    __internal
    get el() { return this.__internal.el }
    get element() { return this.__internal.el }
    get app() { return this.__internal.app }
    get identifier() { return this.constructor.identifier }
    constructor(el, app, attributes = {}) {
        this.__internal = { el, app }
        this.constructor.attributeSyncer.initializeAttributes(this, attributes)
        !el.nxs_aspects ? el.nxs_aspects = new Map() : null
        el.nxs_aspects.set(this.identifier, this)
        this.initialize()
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

    initialize()  { return this }
    connect() { return this }
    disconnect() { return this }
    attributeChanged(name, oldValue, newValue) { }
}