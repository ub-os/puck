import { getElement } from "../General/Utility";

class ObserverManager {
    static __instance = null;
    static get inst() {
        if (!ObserverManager.__instance) {
            ObserverManager.__instance = new ObserverManager();
        }
        return ObserverManager.__instance
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

    __idx = 0
    __elIdx = 0
    __callbackIdx = 0
    __idMap = {}
    __callbacks = {
        [IntersectionObserver.name]: [],
        [ResizeObserver.name]: [],
        [MutationObserver.name]: [],
    }
    __observers = {
        [IntersectionObserver.name]: {},
        [ResizeObserver.name]: {},
        [MutationObserver.name]: {},
    }
    defaultIntersectionOptions = { root: null, rootMargin: '0px', threshold: 0 }
    defaultMutationOptions = { attributes: true, childList: true, subtree: true }
    defaultResizeOptions = { box: 'border-box' }

    constructor() {
        if (ObserverManager.__instance) {
            console.warn('ObserverManager is a singleton. Use "ObserverManager.inst()" instead of creating a new instance with "new ObserverManager()".');
            return ObserverManager.__instance;
        }
    }

    __observe({ obsName, id, target, callbackId, opts }) {
        if (this.__idMap[id]) {
            console.warn(`ObserverManager: id "${id}" is already in use. Skipping.`)
            return
        }
        if (!this.__callbacks[obsName][callbackId]) {
            console.warn(`ObserverManager: callbackId "${callbackId}" is not registered. Skipping.`)
            return
        }
        const el = getElement(target)
        if (!el.id) {
            el.id = 'pux-observer-manager-' + this.__elIdx++
        }
        id = id || (this.__idx++).toString()
        const elId = el.id
        let obsId = this.objectIdentifierString(opts)
        if (obsName === MutationObserver.name && opts.subtree) {
            obsId = elId + '---' + obsId
        }
        this.__idMap[id] = {
            elId,
            obsId,
            callbackId,
            obsName,
        }
/*        if (!this.__elMap[elId]) {
            this.__elMap[elId] = {
                ids: [id],
                callbackIds: [callbackId],
                [ResizeObserver.name]: [],
                [IntersectionObserver.name]: [],
                [MutationObserver.name]: [],
                [obsName]: [obsId]
            }
        } else {
            this.__elMap[elId].ids.push(id)
            this.__elMap[elId].callbackIds.push(callbackId)
            this.__elMap[elId][obsName].push(obsId)
        }*/
        let obs = this.__observers[obsName][obsId]
        if (!obs) {
            if (obsName === MutationObserver.name && opts.subtree) {
                obs = new window[obsName]((mutations, observer) => {
                    observer.puxManager.callbacksByElId[elId].forEach((cbId, i) => {
                        if (this.__callbacks[obsName][cbId]) {
                            this.__callbacks[obsName][cbId](mutations, observer, el)
                        } else {
                            this.__callbackDeletedHandler(observer, elId, i, obsName, obsId)
                        }
                    })
                })
            } else if (obsName === MutationObserver.name) {
                obs = new window[obsName]((mutations, observer) => {
                    observer.puxManager.callbacksByElId[mutations[0].target.id].forEach((cbId, i) => {
                        if (this.__callbacks[obsName][cbId]) {
                            this.__callbacks[obsName][cbId](mutations, observer, el)
                        } else {
                            this.__callbackDeletedHandler(observer, elId, i, obsName, obsId)
                        }
                    })
                })
            } else {
                obs = new window[obsName]((entries, observer) => {
                    for (let entry of entries) {
                        observer.puxManager.callbacksByElId[entry.target.id].forEach((cbId, i) => {
                            if (this.__callbacks[obsName][cbId]) {
                                this.__callbacks[obsName][cbId](entry, observer)
                            } else {
                                this.__callbackDeletedHandler(observer, elId, i, obsName, obsId)
                            }
                        })
                    }},
                    opts)
            }
            obs.puxManager = { callbacksByElId: {} }
            this.__observers[obsName][obsId] = obs
        }
        const callbacksByElId = this.__observers[obsName][obsId].puxManager.callbacksByElId
        if (!callbacksByElId[elId]) {
            callbacksByElId[elId] = []
        }
        callbacksByElId[elId].push(callbackId)
        this.__observers[obsName][obsId].observe(el, opts)
        return id
    }

    __callbackDeletedHandler(observer, elId, i, obsName, obsId) {
        observer.puxManager.callbacksByElId[elId].splice(i, 1)
        if (!observer.puxManager.callbacksByElId[elId]) {
            delete observer.puxManager.callbacksByElId[elId]
        }
        if (!Object.keys(observer.puxManager.callbacksByElId).length) {
            observer.disconnect()
            delete this.__observers[obsName][obsId]
        }
    }

    __unobserve(id) {
        const map = this.__idMap[id]
        if (!map) {
            console.warn(`ObserverManager: id "${id}" does not exist. Skipping detachment.`)
            return
        }
        const callbacksByElId = this.__observers[map.obsName][map.obsId].puxManager.callbacksByElId
        if (Object.keys(callbacksByElId).length === 1 && callbacksByElId[map.elId].length === 1) {
            this.__observers[map.obsName][map.obsId].disconnect()
            delete this.__observers[map.obsName][map.obsId]
            return
        }
        const fnIndex = callbacksByElId[map.elId].indexOf(map.fn)
        callbacksByElId[map.elId].splice(fnIndex, 1)
        if (!callbacksByElId[map.elId].length) {
            if (this.__observers[map.obsName][map.obsId].unobserve) {
                this.__observers[map.obsName][map.obsId].unobserve(document.getElementById(map.elId))
            }
            delete callbacksByElId[map.elId]
        }
    }
    __registerCallback(fn, obsName) {
        const id = this.__callbackIdx++
        this.__callbacks[obsName][id] = fn
        return id
    }

    __getCallbackObject(id, obsName, defaultOpts) {
        const self = this
        return {
            id,
            add(target, opts = defaultOpts) {
                return self.__observe({ obsName, target, opts, callbackId: id, id: '' })
            },
            addById(id, target, opts = defaultOpts) {
                return self.__observe({ obsName, target, opts, callbackId: id, id })
            },
            remove(id) {
                self.__unobserve(id)
                return id
            },
            delete() {
                delete self.__callbacks[obsName][id]
            }
        }
    }

    __singleManager(obsName, defaultOpts) {
        return {
            sharedCallback: (fn) => {
                return this.__getCallbackObject(this.__registerCallback(fn, obsName), obsName, defaultOpts)
            },
            add: (target, fn, opts = defaultOpts) => {
                const callbackId = this.__registerCallback(fn, obsName, defaultOpts)
                return this.__observe({ obsName, target, opts, callbackId, id: '' })
            },
            addById: (id, target, fn, opts = defaultOpts) => {
                const callbackId = this.__registerCallback(fn, obsName, defaultOpts)
                return this.__observe({ obsName, target, opts, callbackId, id })
            },
            remove: (id) => {
                this.__unobserve(id)
            },
            disconnectAll: () => {
                for (let obsId in this.__observers[obsName]) {
                    this.__observers[obsName][obsId].disconnect()
                }
                this.__observers[obsName] = {}
            }
        }
    }
    get resize() {
        const obsName = ResizeObserver.name
        return this.__singleManager(obsName, this.defaultResizeOptions)
    }

    get intersection() {
        const obsName = IntersectionObserver.name
        return this.__singleManager(obsName, this.defaultIntersectionOptions)
    }

    get mutation() {
        const obsName = MutationObserver.name
        return this.__singleManager(obsName, this.defaultMutationOptions)
    }

    disconnect() {
        this.resize.disconnectAll()
        this.intersection.disconnectAll()
        this.mutation.disconnectAll()
    }

    destroy() {
        this.disconnect()
        this.__idx = 0
        this.__elIdx = 0
        this.__callbackIdx = 0
        this.__idMap = {}
        this.__callbacks = {
            [IntersectionObserver.name]: [],
            [ResizeObserver.name]: [],
            [MutationObserver.name]: [],
        }
        this.__observers = {
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

