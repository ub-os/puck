import config from "./Config"

export default class ElementConnection {
    /**
     * @type {HTMLElement}
     */
    el = null
    identifier = null
    name = null
    aspect = null

    constructor(el, descriptor) {
        this.el = el
        const [
            aspectIdentifier,
            name,
            id
        ] = descriptor.split(/[.#]/)
        if (!name || !aspectIdentifier) return
        const aspectEl = id
            ? document.getElementById(id)
            : el.closest(`[${config.attributePrefix}scope*=" ${aspectIdentifier} "]`);
        this.aspect = aspectEl?.nxs_aspects?.get(aspectIdentifier)
        if (!this.aspect) return

        this.identifier = `${aspectIdentifier}.${name}`
        this.name = name
        !el.nxs_connections ? el.nxs_connections = new Map() : null
        el.nxs_connections.set(this.identifier, this)
    }

    connect() {
        this.aspect[`${this.name}Elements`].add(this.el)
        if (typeof this.aspect[`${this.name}ElementConnected`] == 'function') {
            this.aspect[`${this.name}ElementConnected`](this.el)
        }
    }

    disconnect() {
        this.aspect[`${this.name}Elements`].delete(this.el)
        if (typeof this.aspect[`${this.name}ElementDisconnected`] == 'function') {
            this.aspect[`${this.name}ElementDisconnected`](this.el)
        }
    }
}