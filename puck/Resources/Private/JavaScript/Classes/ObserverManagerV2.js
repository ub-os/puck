import { getElement } from "../General/Functions.js";

class ObserverManager {
    static ____instance = null;
    static get inst() {
        if (!ObserverManager.____instance) {
            ObserverManager.____instance = new ObserverManager();
        }
        return ObserverManager.____instance
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

    ____idx = 0
    ____elIdx = 0
    ____callbackIdx = 0
    ____idMap = {}
    ____callbacks = {
        [IntersectionObserver.name]: [],
        [ResizeObserver.name]: [],
        [MutationObserver.name]: [],
    }
    ____observers = {
        [IntersectionObserver.name]: {},
        [ResizeObserver.name]: {},
        [MutationObserver.name]: {},
    }
    defaultIntersectionOptions = { root: null, rootMargin: '0px', threshold: 0 }
    defaultMutationOptions = { attributes: true, childList: true, subtree: true }
    defaultResizeOptions = { box: 'border-box' }

    constructor() {
        if (ObserverManager.____instance) {
            console.warn('ObserverManager is a singleton. Use "ObserverManager.inst()" instead of creating a new instance with "new ObserverManager()".');
            return ObserverManager.____instance;
        }
    }

    ____observe({ obsName, id, target, callbackId, opts }) {
        if (this.____idMap[id]) {
            console.warn(`ObserverManager: id "${id}" is already in use. Skipping.`)
            return
        }
        if (!this.____callbacks[obsName][callbackId]) {
            console.warn(`ObserverManager: callbackId "${callbackId}" is not registered. Skipping.`)
            return
        }
        const el = getElement(target)
        if (!el.id) {
            el.id = 'pux-observer-manager-' + this.____elIdx++
        }
        id = id || (this.____idx++).toString()
        const elId = el.id
        let obsId = this.objectIdentifierString(opts)
        if (obsName === MutationObserver.name && opts.subtree) {
            obsId = elId + '---' + obsId
        }
        this.____idMap[id] = {
            elId,
            obsId,
            callbackId,
            obsName,
        }
/*        if (!this.____elMap[elId]) {
            this.____elMap[elId] = {
                ids: [id],
                callbackIds: [callbackId],
                [ResizeObserver.name]: [],
                [IntersectionObserver.name]: [],
                [MutationObserver.name]: [],
                [obsName]: [obsId]
            }
        } else {
            this.____elMap[elId].ids.push(id)
            this.____elMap[elId].callbackIds.push(callbackId)
            this.____elMap[elId][obsName].push(obsId)
        }*/
        let obs = this.____observers[obsName][obsId]
        if (!obs) {
            if (obsName === MutationObserver.name && opts.subtree) {
                obs = new window[obsName]((mutations, observer) => {
                    observer.puxManager.callbacksByElId[elId].forEach((cbId, i) => {
                        if (this.____callbacks[obsName][cbId]) {
                            this.____callbacks[obsName][cbId](mutations, observer, el)
                        } else {
                            this.____callbackDeletedHandler(observer, elId, i, obsName, obsId)
                        }
                    })
                })
            } else if (obsName === MutationObserver.name) {
                obs = new window[obsName]((mutations, observer) => {
                    observer.puxManager.callbacksByElId[mutations[0].target.id].forEach((cbId, i) => {
                        if (this.____callbacks[obsName][cbId]) {
                            this.____callbacks[obsName][cbId](mutations, observer, el)
                        } else {
                            this.____callbackDeletedHandler(observer, elId, i, obsName, obsId)
                        }
                    })
                })
            } else {
                obs = new window[obsName]((entries, observer) => {
                    for (let entry of entries) {
                        observer.puxManager.callbacksByElId[entry.target.id].forEach((cbId, i) => {
                            if (this.____callbacks[obsName][cbId]) {
                                this.____callbacks[obsName][cbId](entry, observer)
                            } else {
                                this.____callbackDeletedHandler(observer, elId, i, obsName, obsId)
                            }
                        })
                    }},
                    opts)
            }
            obs.puxManager = { callbacksByElId: {} }
            this.____observers[obsName][obsId] = obs
        }
        const callbacksByElId = this.____observers[obsName][obsId].puxManager.callbacksByElId
        if (!callbacksByElId[elId]) {
            callbacksByElId[elId] = []
        }
        callbacksByElId[elId].push(callbackId)
        this.____observers[obsName][obsId].observe(el, opts)
        return id
    }

    ____callbackDeletedHandler(observer, elId, i, obsName, obsId) {
        observer.puxManager.callbacksByElId[elId].splice(i, 1)
        if (!observer.puxManager.callbacksByElId[elId]) {
            delete observer.puxManager.callbacksByElId[elId]
        }
        if (!Object.keys(observer.puxManager.callbacksByElId).length) {
            observer.disconnect()
            delete this.____observers[obsName][obsId]
        }
    }

    ____unobserve(id) {
        const map = this.____idMap[id]
        if (!map) {
            console.warn(`ObserverManager: id "${id}" does not exist. Skipping detachment.`)
            return
        }
        const callbacksByElId = this.____observers[map.obsName][map.obsId].puxManager.callbacksByElId
        if (Object.keys(callbacksByElId).length === 1 && callbacksByElId[map.elId].length === 1) {
            this.____observers[map.obsName][map.obsId].disconnect()
            delete this.____observers[map.obsName][map.obsId]
            return
        }
        const fnIndex = callbacksByElId[map.elId].indexOf(map.fn)
        callbacksByElId[map.elId].splice(fnIndex, 1)
        if (!callbacksByElId[map.elId].length) {
            if (this.____observers[map.obsName][map.obsId].unobserve) {
                this.____observers[map.obsName][map.obsId].unobserve(document.getElementById(map.elId))
            }
            delete callbacksByElId[map.elId]
        }
    }
    ____registerCallback(fn, obsName) {
        const id = this.____callbackIdx++
        this.____callbacks[obsName][id] = fn
        return id
    }

    ____getCallbackObject(id, obsName, defaultOpts) {
        const self = this
        return {
            id,
            add(target, opts = defaultOpts) {
                return self.____observe({ obsName, target, opts, callbackId: id, id: '' })
            },
            addById(id, target, opts = defaultOpts) {
                return self.____observe({ obsName, target, opts, callbackId: id, id })
            },
            remove(id) {
                self.____unobserve(id)
                return id
            },
            delete() {
                delete self.____callbacks[obsName][id]
            }
        }
    }

    ____singleManager(obsName, defaultOpts) {
        return {
            sharedCallback: (fn) => {
                return this.____getCallbackObject(this.____registerCallback(fn, obsName), obsName, defaultOpts)
            },
            add: (target, fn, opts = defaultOpts) => {
                const callbackId = this.____registerCallback(fn, obsName, defaultOpts)
                return this.____observe({ obsName, target, opts, callbackId, id: '' })
            },
            addById: (id, target, fn, opts = defaultOpts) => {
                const callbackId = this.____registerCallback(fn, obsName, defaultOpts)
                return this.____observe({ obsName, target, opts, callbackId, id })
            },
            remove: (id) => {
                this.____unobserve(id)
            },
            disconnectAll: () => {
                for (let obsId in this.____observers[obsName]) {
                    this.____observers[obsName][obsId].disconnect()
                }
                this.____observers[obsName] = {}
            }
        }
    }
    get resize() {
        const obsName = ResizeObserver.name
        return this.____singleManager(obsName, this.defaultResizeOptions)
    }

    get intersection() {
        const obsName = IntersectionObserver.name
        return this.____singleManager(obsName, this.defaultIntersectionOptions)
    }

    get mutation() {
        const obsName = MutationObserver.name
        return this.____singleManager(obsName, this.defaultMutationOptions)
    }

    disconnect() {
        this.resize.disconnectAll()
        this.intersection.disconnectAll()
        this.mutation.disconnectAll()
    }

    destroy() {
        this.disconnect()
        this.____idx = 0
        this.____elIdx = 0
        this.____callbackIdx = 0
        this.____idMap = {}
        this.____callbacks = {
            [IntersectionObserver.name]: [],
            [ResizeObserver.name]: [],
            [MutationObserver.name]: [],
        }
        this.____observers = {
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

