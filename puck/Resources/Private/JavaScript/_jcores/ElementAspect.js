export default class ElementAspect {
    static attributes = {
        asleep: false
    }
    static connectedElements = []
    static injectedAspects = {}
    static identifier = null
    static registerCallback() { }

    __initialized = false
    __connected = false
    __identifier = null
    app = null
    /**
     * @type {HTMLElement}
     */
    element = null
    get el() { return this.element }
    set el(el) { this.element = el }
    constructor(el, attributes = {}) {
        this.el = el
        this.__identifier = this.constructor.identifier
        this.constructor.attributeSyncer.initialize(this, attributes)
        !el.jc_aspects ? el.jc_aspects = new Map() : null
        el.jc_aspects.set(this.__identifier, this)
        this.initialize()
        this.__initialized = true
    }

    dispatch(type, options = {}) {
        this.el.dispatchEvent(new CustomEvent(type, options))
    }

    asleepChanged(oldVal, newVal) {
        if (newVal === true && this.__connected) {
            this.disconnect()
            this.__connected = false
        } else if (newVal === false && !this.__connected) {
            this.connect()
            this.__connected = true
        }
    }

    initialize()  { return this }
    connect() { return this }
    disconnect() { return this }
    attributeChanged(name, oldValue, newValue) { }
}