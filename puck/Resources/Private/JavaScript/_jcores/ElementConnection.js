import config from "./Config"

export default class ElementConnection {
    /**
     * @type {HTMLElement}
     */
    el = null
    identifier = null
    name = null
    core = null
    connected = false

    constructor(el, descriptor) {
        this.el = el
        const [
            coreIdentifier,
            name,
            id
        ] = descriptor.split(/[.#]/)
        if (!name || !coreIdentifier) return
        const coreEl = id ? document.getElementById(id) : el.closest(`[${config.attributePrefix}${config.coreAttribute}]`)
        this.core = coreEl?.jc_aspects?.get(coreIdentifier)
        if (!this.core) return

        this.identifier = `${coreIdentifier}.${name}`
        this.name = name
        !el['jc_connections'] ? el.jc_connections = new Map() : null
        el.jc_connections.set(this.identifier, this)
    }

    connect() {
        if (this.connected) return
        this.core[`${this.name}Elements`].add(this.el)
        if (typeof this.core[`${this.name}ElementConnected`] == 'function') {
            this.core[`${this.name}ElementConnected`](this.el)
        }
        this.connected = true
    }

    disconnect() {
        if (!this.connected) return
        this.core[`${this.name}Elements`].delete(this.el)
        if (typeof this.core[`${this.name}ElementDisconnected`] == 'function') {
            this.core[`${this.name}ElementDisconnected`](this.el)
        }
        this.connected = false
    }
}