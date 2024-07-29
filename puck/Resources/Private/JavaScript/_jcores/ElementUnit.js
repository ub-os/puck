
export default class ElementUnit {
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
        const coreEl = id ? document.getElementById(id) : el.closest(`[data-core]`)
        this.core = coreEl?.jc_cores?.get(coreIdentifier)
        if (!this.core) return

        this.identifier = `${coreIdentifier}.${name}`
        this.name = name
        !el['jc_elementUnits'] ? el.jc_elementUnits = new Map() : null
        el.jc_elementUnits.set(this.identifier, this)
    }

    connect() {
        if (this.connected) return
        this.core[`${this.name}Elements`].set(this.el.id, this.el)
        if (typeof this.core[`${this.name}ElementConnected`] == 'function') {
            this.core[`${this.name}ElementConnected`](this.el)
        }
        this.connected = true
    }

    disconnect() {
        if (!this.connected) return
        this.core[`${this.name}Elements`].delete(this.el.id)
        if (typeof this.core[`${this.name}ElementDisconnected`] == 'function') {
            this.core[`${this.name}ElementDisconnected`](this.el)
        }
        this.connected = false
    }
}