import { kebabCase, jsonParse } from "./Utility/StringUtility"
import ListenerCollector from "./Service/ListenerCollector"

export default class Core {
    static attributes = {
        asleep: false
    }
    static units = {}
    static injects = {}
    static triggerables = {}
    static identifier = null
    static registerCallback() { }

    __initialized = false
    __connected = false
    /**
     * @type {HTMLElement}
     */
    __el = null
    listeners = new ListenerCollector()
    identifier = null
    get el() { return this.__el }
    set el(el) { this.__el = el }

    constructor(el, attributes = {}) {
        this.el = el
        this.identifier = this.constructor.identifier
        this.constructor.attributeSyncer.initialize(this, attributes)
        !el['_jcCores'] ? el._jcCores = new Map() : null
        el._jcCores.set(this.identifier, this)
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