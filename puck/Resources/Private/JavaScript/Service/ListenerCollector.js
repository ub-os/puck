export default class ListenerCollector {

    #listeners = {} // # in a JS class signifies private
    #idx = 1

    // add event listener, returns integer ID of new listener
    add(element, type, listener, options = {}) {
        this.#privateAddEventListener(element, this.#idx++, type, listener, options)
        return this.#idx
    }

    // add event listener with custom ID (avoids need to retrieve return ID since you are providing it yourself)
    addById(id) {
        return (element, type, listener, options = {}) => {
            this.#privateAddEventListener(element, id, type, listener, options)
            return id
        }
    }

    addDelegate(element, selector, type, callback, options = {}) {
        this.#privateAddDelegateEventListener(element, this.#idx++, selector, type, callback, options)
        return this.#idx
    }


    addDelegateById(id) {
        return (element, selector, type, callback, options = {}) => {
            this.#privateAddDelegateEventListener(element, id, selector, type, callback, options)
            return id
        }
    }

    #privateAddDelegateEventListener(element, id, selector, type, listener, options) {
        const delegateListener = (event) => {
            const target = event.target.closest(selector)
            if (target) {
                event.delegateTarget = target
                listener(event)
            }
        }
        this.#privateAddEventListener(element, id, type, delegateListener, options)
    }

    #privateAddEventListener(element, id, type, listener, options) {
        if (this.#listeners[id]) throw Error(`A listener with id ${id} already exists`)
        element.addEventListener(type, listener, options)
        this.#listeners[id] = {element, type, listener, options}
    }

    // remove event listener with given ID, returns ID of removed listener or null (if listener with given ID does not exist)
    remove(id) {
        const listen = this.#listeners[id]
        if (listen) {
            listen.element.removeEventListener(listen.type, listen.listener, listen.options)
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