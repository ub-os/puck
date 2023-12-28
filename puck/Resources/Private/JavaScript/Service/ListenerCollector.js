export default class ListenerCollector {

    #listeners = {} // # in a JS class signifies private
    #idx = 1

    // add event listener, returns integer ID of new listener
    add(element, type, listener, useCapture = false) {
        this.#privateAddEventListener(element, this.#idx++, type, listener, useCapture)
        return this.#idx
    }

    // add event listener with custom ID (avoids need to retrieve return ID since you are providing it yourself)
    addById(element, id, type, listener, useCapture = false) {
        this.#privateAddEventListener(element, id, type, listener, useCapture)
        return id
    }

    #privateAddEventListener(element, id, type, listener, useCapture) {
        if (this.#listeners[id]) throw Error(`A listener with id ${id} already exists`)
        element.addEventListener(type, listener, useCapture)
        this.#listeners[id] = {element, type, listener, useCapture}
    }

    // remove event listener with given ID, returns ID of removed listener or null (if listener with given ID does not exist)
    remove(id) {
        const listen = this.#listeners[id]
        if (listen) {
            listen.element.removeEventListener(listen.type, listen.listener, listen.useCapture)
            delete this.#listeners[id]
        }
        return listen || null
    }

    has(id) {
        return !!this.#listeners[id]
    }

    // remove all event listeners
    removeAll() {
        for (const id in this.#listeners) {
            this.remove(id)
        }
    }
    // returns number of events listeners
    get length() {
        return Object.keys(this.#listeners).length
    }
    // returns array of event listener IDs
    get ids() {
        return Object.keys(this.#listeners)
    }
    destroy() {
        this.removeAll()
    }

}