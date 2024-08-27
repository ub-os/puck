import config from "./Config"

export default class ElementConnection {
    connected = false
    constructor(el, descriptor) {
        this.el = el
        const [
            aspectIdentifier,
            type,
            hostId
        ] = descriptor.split(/[.#]/)
        if (!type || !aspectIdentifier) return
        this.type = type
        this.hostId = hostId
        this.aspectIdentifier = aspectIdentifier
        this.aspectEl = hostId
            ? document.getElementById(hostId)
            : el.closest(`[${config.attributePrefix}scope*=" ${aspectIdentifier} "]`)
        this.aspect = this.aspectEl?.nxs_aspects?.get(aspectIdentifier)
        !el.nxs_connections ? el.nxs_connections = new Map() : null
        el.nxs_connections.set(descriptor, this)
    }

    connect() {
        if (this.connected || !this.aspect) return
        this.aspect.__internal.elements.get(this.type)?.add(this.el)
        if (typeof this.aspect[`${this.type}ElementConnected`] == 'function') {
            this.aspect[`${this.type}ElementConnected`](this.el)
        }
        this.connected = true
    }

    disconnect() {
        if (!this.connected || !this.aspect) return
        this.aspect.__internal.elements.get(this.type)?.delete(this.el)
        if (typeof this.aspect[`${this.type}ElementDisconnected`] == 'function') {
            this.aspect[`${this.type}ElementDisconnected`](this.el)
        }
        this.connected = false
    }
}