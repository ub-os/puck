import { getElement } from "../General/Utility";

class ObserverManager {
    static #instance = null;
    static get inst() {
        if (!ObserverManager.#instance) {
            ObserverManager.#instance = new ObserverManager();
        }
        return ObserverManager.#instance
    }
    static get resize() {
        return ObserverManager.inst.resize
    }
    static get intersection() {
        return ObserverManager.inst.intersection
    }
    static get mutation() {
        return ObserverManager.inst.mutation
    }
    objectIdentifierString(obj){
        const sortedKeys = Object.keys(obj).sort()
        const arr = []
        sortedKeys.forEach((key, i) => {
            let val = obj[key]
            key = JSON.stringify(key);
            val = JSON.stringify(val);
            arr.push(key + ':' + val);
        })
        return "{" + arr.join(",") + "}";
    }

    #idx = 0
    #elIdx = 0
    #callbackIdx = 0
    #idMap = {}
    #callbacks = {
        [IntersectionObserver.name]: [],
        [ResizeObserver.name]: [],
        [MutationObserver.name]: [],
    }
    #observers = {
        [IntersectionObserver.name]: {},
        [ResizeObserver.name]: {},
        [MutationObserver.name]: {},
    }
    defaultIntersectionOptions = { root: null, rootMargin: '0px', threshold: 0 }
    defaultMutationOptions = { attributes: true, childList: true, subtree: true }
    defaultResizeOptions = { box: 'border-box' }

    get properties() {
        return {
            idx: this.#idx,
            elIdx: this.#elIdx,
            callbackIdx: this.#callbackIdx,
            idMap: this.#idMap,
            callbacks: this.#callbacks,
            observers: this.#observers,
        }
    }
    constructor() {
        if (ObserverManager.#instance) {
            console.warn('ObserverManager is a singleton. Use "ObserverManager.inst()" instead of creating a new instance with "new ObserverManager()".');
            return ObserverManager.#instance;
        }
    }

    #observe({ obsName, id, target, callbackId, opts }) {
        if (this.#idMap[id]) {
            console.warn(`ObserverManager: id "${id}" is already in use. Skipping.`)
            return
        }
        if (!this.#callbacks[obsName][callbackId]) {
            console.warn(`ObserverManager: callbackId "${callbackId}" is not registered. Skipping.`)
            return
        }
        const el = getElement(target)
        if (!el.id) {
            el.id = 'pux-observer-manager-' + this.#elIdx++
        }
        id = id || (this.#idx++).toString()
        const elId = el.id
        let obsId = this.objectIdentifierString(opts)
        if (obsName === MutationObserver.name && opts.subtree) {
            obsId = elId + '---' + obsId
        }
        this.#idMap[id] = {
            elId,
            obsId,
            callbackId,
            obsName,
        }
/*        if (!this.#elMap[elId]) {
            this.#elMap[elId] = {
                ids: [id],
                callbackIds: [callbackId],
                [ResizeObserver.name]: [],
                [IntersectionObserver.name]: [],
                [MutationObserver.name]: [],
                [obsName]: [obsId]
            }
        } else {
            this.#elMap[elId].ids.push(id)
            this.#elMap[elId].callbackIds.push(callbackId)
            this.#elMap[elId][obsName].push(obsId)
        }*/
        let obs = this.#observers[obsName][obsId]
        if (!obs) {
            if (obsName === MutationObserver.name && opts.subtree) {
                obs = new window[obsName]((mutations, observer) => {
                    observer.callbacksByElId[elId].forEach((cbId, i) => {
                        if (this.#callbacks[obsName][cbId]) {
                            this.#callbacks[obsName][cbId](mutations, observer, el)
                        } else {
                            this.#callbackDeletedHandler(observer, elId, i, obsName, obsId)
                        }
                    })
                })
            } else if (obsName === MutationObserver.name) {
                obs = new window[obsName]((mutations, observer) => {
                    observer.callbacksByElId[mutations[0].target.id].forEach((cbId, i) => {
                        if (this.#callbacks[obsName][cbId]) {
                            this.#callbacks[obsName][cbId](mutations, observer, el)
                        } else {
                            this.#callbackDeletedHandler(observer, elId, i, obsName, obsId)
                        }
                    })
                })
            } else {
                obs = new window[obsName]((entries, observer) => {
                    for (let entry of entries) {
                        observer.callbacksByElId[entry.target.id].forEach((cbId, i) => {
                            if (this.#callbacks[obsName][cbId]) {
                                this.#callbacks[obsName][cbId](entry, observer)
                            } else {
                                this.#callbackDeletedHandler(observer, elId, i, obsName, obsId)
                            }
                        })
                    }},
                    opts)
            }
            obs.callbacksByElId = {}
            this.#observers[obsName][obsId] = obs
        }
        const callbacksByElId = this.#observers[obsName][obsId].callbacksByElId
        if (!callbacksByElId[elId]) {
            callbacksByElId[elId] = []
        }
        callbacksByElId[elId].push(callbackId)
        this.#observers[obsName][obsId].observe(el, opts)
        return id
    }

    #callbackDeletedHandler(observer, elId, i, obsName, obsId) {
        observer.callbacksByElId[elId].splice(i, 1)
        if (!observer.callbacksByElId[elId]) {
            delete observer.callbacksByElId[elId]
        }
        if (!Object.keys(observer.callbacksByElId).length) {
            observer.disconnect()
            delete this.#observers[obsName][obsId]
        }
    }

    #unobserve(id) {
        const map = this.#idMap[id]
        if (!map) {
            console.warn(`ObserverManager: id "${id}" does not exist. Skipping detachment.`)
            return
        }
        const callbacksByElId = this.#observers[map.obsName][map.obsId].callbacksByElId
        if (Object.keys(callbacksByElId).length === 1 && callbacksByElId[map.elId].length === 1) {
            this.#observers[map.obsName][map.obsId].disconnect()
            delete this.#observers[map.obsName][map.obsId]
            return
        }
        const fnIndex = callbacksByElId[map.elId].indexOf(map.fn)
        callbacksByElId[map.elId].splice(fnIndex, 1)
        if (!callbacksByElId[map.elId].length) {
            if (this.#observers[map.obsName][map.obsId].unobserve) {
                this.#observers[map.obsName][map.obsId].unobserve(document.getElementById(map.elId))
            }
            delete callbacksByElId[map.elId]
        }
    }

    #registerCallback(fn, obsName) {
        const id = this.#callbackIdx++
        this.#callbacks[obsName][id] = fn
        return id
    }

    #getCallbackObject(id, obsName, defaultOpts) {
        const self = this
        return {
            id,
            add(target, opts = defaultOpts) {
                return self.#observe({ obsName, target, opts, callbackId: id, id: '' })
            },
            addById(id, target, opts = defaultOpts) {
                return self.#observe({ obsName, target, opts, callbackId: id, id })
            },
            remove(id) {
                self.#unobserve(id)
                return id
            },
            delete() {
                delete self.#callbacks[obsName][id]
            }
        }
    }

    #singleManager(obsName, defaultOpts) {
        return {
            sharedCallback: (fn) => {
                return this.#getCallbackObject(this.#registerCallback(fn, obsName), obsName, defaultOpts)
            },
            add: (target, fn, opts = defaultOpts) => {
                const callbackId = this.#registerCallback(fn, obsName, defaultOpts)
                return this.#observe({ obsName, target, opts, callbackId, id: '' })
            },
            addById: (id, target, fn, opts = defaultOpts) => {
                const callbackId = this.#registerCallback(fn, obsName, defaultOpts)
                return this.#observe({ obsName, target, opts, callbackId, id })
            },
            remove: (id) => {
                this.#unobserve(id)
            },
            disconnectAll: () => {
                for (let obsId in this.#observers[obsName]) {
                    this.#observers[obsName][obsId].disconnect()
                }
                this.#observers[obsName] = {}
            }
        }
    }
    get resize() {
        const obsName = ResizeObserver.name
        return this.#singleManager(obsName, this.defaultResizeOptions)
    }

    get intersection() {
        const obsName = IntersectionObserver.name
        return this.#singleManager(obsName, this.defaultIntersectionOptions)
    }

    get mutation() {
        const obsName = MutationObserver.name
        return this.#singleManager(obsName, this.defaultMutationOptions)
    }

    disconnect() {
        this.resize.disconnectAll()
        this.intersection.disconnectAll()
        this.mutation.disconnectAll()
    }

    destroy() {
        this.disconnect()
        this.#idx = 0
        this.#elIdx = 0
        this.#callbackIdx = 0
        this.#idMap = {}
        this.#callbacks = {
            [IntersectionObserver.name]: [],
            [ResizeObserver.name]: [],
            [MutationObserver.name]: [],
        }
        this.#observers = {
            [IntersectionObserver.name]: {},
            [ResizeObserver.name]: {},
            [MutationObserver.name]: {},
        }
    }
}

const ResizeManager = ObserverManager.resize
const IntersectionManager = ObserverManager.intersection
const MutationManager = ObserverManager.mutation

export { ObserverManager, ResizeManager, IntersectionManager, MutationManager }

