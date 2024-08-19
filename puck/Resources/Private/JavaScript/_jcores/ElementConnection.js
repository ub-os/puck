import config from "./Config"

export default class ElementConnection {
    /**
     * @type {HTMLElement}
     */
    el = null
    identifier = null
    name = null
    aspect = null
    connected = false

    constructor(el, descriptor) {
        this.el = el
        const [
            aspectIdentifier,
            name,
            id
        ] = descriptor.split(/[.#]/)
        if (!name || !aspectIdentifier) return
        const attr = `${config.attributePrefix}${config.connectAttribute}`
        const aspectEl = id
            ? document.getElementById(id)
            : el.closest(`[${attr}="${aspectIdentifier}"], [${attr}^="${aspectIdentifier} "], [${attr}$=" ${aspectIdentifier}"], [${attr}*=" ${aspectIdentifier} "]`);
        this.aspect = aspectEl?.jc_aspects?.get(aspectIdentifier)
        if (!this.aspect) return

        this.identifier = `${aspectIdentifier}.${name}`
        this.name = name
        !el.jc_connections ? el.jc_connections = new Map() : null
        el.jc_connections.set(this.identifier, this)
    }

    connect() {
        if (this.connected) return
        this.aspect[`${this.name}Elements`].add(this.el)
        if (typeof this.aspect[`${this.name}ElementConnected`] == 'function') {
            this.aspect[`${this.name}ElementConnected`](this.el)
        }
        this.connected = true
    }

    disconnect() {
        if (!this.connected) return
        this.aspect[`${this.name}Elements`].delete(this.el)
        if (typeof this.aspect[`${this.name}ElementDisconnected`] == 'function') {
            this.aspect[`${this.name}ElementDisconnected`](this.el)
        }
        this.connected = false
    }
}