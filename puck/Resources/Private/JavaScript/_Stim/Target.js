import { $id } from "./Utility/DomUtility"

export default class Target {
    /**
     * @type {HTMLElement}
     */
    el = null
    identifier = null
    name = null
    controller = null
    connected = false
    constructor(el, descriptor) {
        this.el = el
        const [
            controllerIdentifier,
            name,
            id
        ] = descriptor.split(/[.#]/)
        if (!name || !controllerIdentifier) return
        const controllerElement = id ? $id(id) : el.closest(`[data-controller]`)
        this.controller = controllerElement?.stimControllers?.get(controllerIdentifier)
        if (!this.controller) return

        this.identifier = `${controllerIdentifier}.${name}`
        this.name = name
        !el['stimTargets'] ? el.stimTargets = new Map() : null
        el.stimTargets.set(this.identifier, this)
    }

    connect() {
        if (this.connected) return
        this.controller[`${this.name}Targets`].set(this.el.id, this.el)
        if (typeof this.controller[`${this.name}Connected`] == 'function') {
            this.controller[`${this.name}Connected`](this.el)
        }
        this.connected = true
    }

    disconnect() {
        if (!this.connected) return
        this.controller[`${this.name}Targets`].delete(this.el.id)
        if (typeof this.controller[`${this.name}Disconnected`] == 'function') {
            this.controller[`${this.name}Disconnected`](this.el)
        }
        this.connected = false
    }
}