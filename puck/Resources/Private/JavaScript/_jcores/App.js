import Registry from "./Registry"
import Connector from "./Connector"
import config from "./Config"

class App {
    config = config
    registry = new Registry()
    connector = new Connector(this.registry)
    registerAspect(identifier, constructor) {
        this.registry.registerAspect(identifier, constructor)
    }
    registerAspectCustomElement(identifier) {
        this.registry.registerAspectCustomElement(identifier)
    }
    registerConnectedCallback(selector, callback) {
        this.registry.registerConnectedCallback(selector, callback)
    }
    connect() {
        this.connector.connect()
    }
    disconnect() {
        this.connector.disconnect()
    }
    getAspects(el) {
        return el?.nxs_aspects
    }

    getAspect(el, aspectName) {
        return el?.nxs_aspects?.get(aspectName)
    }
}

export default new App()