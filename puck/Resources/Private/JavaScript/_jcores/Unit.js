import { $id } from "./Utility/DomUtility"

export default class Unit {
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
        const coreEl = id ? $id(id) : el.closest(`[data-core]`)
        this.core = coreEl?.htmcCores?.get(coreIdentifier)
        if (!this.core) return

        this.identifier = `${coreIdentifier}.${name}`
        this.name = name
        !el['htmcUnits'] ? el.htmcUnits = new Map() : null
        el.htmcUnits.set(this.identifier, this)
    }

    connect() {
        if (this.connected) return
        this.core[`${this.name}Units`].set(this.el.id, this.el)
        if (typeof this.core[`${this.name}UnitConnected`] == 'function') {
            this.core[`${this.name}UnitConnected`](this.el)
        }
        this.connected = true
    }

    disconnect() {
        if (!this.connected) return
        this.core[`${this.name}Units`].delete(this.el.id)
        if (typeof this.core[`${this.name}UnitDisconnected`] == 'function') {
            this.core[`${this.name}UnitDisconnected`](this.el)
        }
        this.connected = false
    }
}