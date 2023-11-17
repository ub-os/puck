import { getElement } from "../General/Functions.js";

class ObserverManager {

    static #instance = null;
    static get(observer = '') {
        if (!ObserverManager.#instance) {
            ObserverManager.#instance = new ObserverManager();
        }
        switch (observer) {
            case 'resize':
                return ObserverManager.#instance.resize()
            case 'intersection':
                return ObserverManager.#instance.intersection()
            case 'mutation':
                return ObserverManager.#instance.mutation()
            default:
                return ObserverManager.#instance
        }
    }

    #id = 0
    #observerMap = new Map();
    #intersectionObservers = new Map();
    #resizeObserver = null;
    #mutationObserver = null;
    defaultIntersectionOptions = { root: null, rootMargin: '0px', threshold: 0 };
    defaultMutationOptions = { attributes: true, childList: true, subtree: true };
    defaultResizeOptions = { box: 'border-box' };
    dataSets = { ResizeObserver: 'omResi', IntersectionObserver: 'omIn', MutationObserver: 'omMu' }

    constructor() {
        if (ObserverManager.#instance) {
            console.warn('ObserverManager is a singleton. Use "ObserverManager.get()" instead of creating a new instance with "new ObserverManager()".');
            return ObserverManager.#instance;
        }
    }

    #getIntersectionObserver(options) {
        const key = JSON.stringify(options)
        if (!this.#intersectionObservers.has(key)) {
            this.#intersectionObservers.set(
                key,
                new IntersectionObserver((entries, observer) => {
                    for (let entry of entries) {
                        for (let id of entry.target.dataset[this.dataSets.IntersectionObserver + 'Ids'].split(',')) {
                            if (!id || id === ',') {
                                continue
                            }
                            const observation = this.#observerMap.get(id)
                            if (!observation) {
                                continue
                            }
                            if (observation.instanceOptions === options) {
                                observation.fn(entry, observer)
                            }
                        }
                    }
                }, options)
            );
        }
        return this.#intersectionObservers.get(key);
    }

    #getResizeObserver() {
        if (!this.#resizeObserver) {
            this.#resizeObserver = new ResizeObserver((entries, observer) => {
                for (let entry of entries) {
                    for (let id of entry.target.dataset[this.dataSets.ResizeObserver + 'Ids'].split(',')) {
                        if (!id || id === ',') {
                            continue
                        }
                        const observation = this.#observerMap.get(id)
                        if (!observation) {
                            continue
                        }
                        observation.fn(entry, observer)
                    }
                }
            })
        }
        return this.#resizeObserver;
    }

    #getMutationObserver() {
        if (!this.#mutationObserver) {
            this.#mutationObserver = new MutationObserver((mutations, observer) => {
                for (let mutation of mutations) {
                    console.log(mutation)
                    for (let id of mutation.target.dataset[this.dataSets.MutationObserver + 'Ids'].split(',')) {
                        if (!id || id === ',') {
                            continue
                        }
                        const observation = this.#observerMap.get(id)
                        if (!observation) {
                            continue
                        }
                        observation.fn(mutation, observer)
                    }
                }
            })
        }
        return this.#mutationObserver
    }

    #observe({
         id,
         observer,
         target,
         fn,
         instanceOptions = null,
         observeMethodOptions = null
     }) {
        const el = getElement(target)

        id = id || (this.#id++).toString()

        if (!el.puxObserverManagerIds) {
            el.puxObserverManagerIds = {}
            if (!el.puxObserverManagerIds[observer.constructor.name]) {
                el.puxObserverManagerIds[observer.constructor.name] = {}
            }
        }
        if (!el.puxObserverManagerIds[observer.constructor.name][id]) {
            el.puxObserverManagerIds[observer.constructor.name][id] = id
        } else {
            console.warn('ObserverManager: Element is already observed by this observer with this id. Skipping.')
            return
        }

        this.#observerMap.set(id, { el, instanceOptions, fn })
        if (observeMethodOptions) {
            observer.observe(el, observeMethodOptions)
        } else {
            observer.observe(el)
        }
    }

    #unobserve({ id, observerName }) {
        if (!this.#observerMap.has(id)) {
            return
        }
        const obs = this.#observerMap.get(id)

        delete obs.element.puxObserverManagerIds[observerName][id]

        if (obs.element.puxObserverManagerIds[observerName] === {}) {
            if (observerName === IntersectionObserver.name) {
                this.#getIntersectionObserver(obs.instanceOptions).unobserve(obs.element)
                if (!this.#getIntersectionObserver(obs.instanceOptions).takeRecords().length) {
                    this.#intersectionObservers.delete(JSON.stringify(obs.instanceOptions))
                }
            }
            if (observerName === ResizeObserver.name) {
                this.#getResizeObserver().unobserve(obs.element)
            }
            if (observerName === MutationObserver.name) {
                //
            }
        }
        this.#observerMap.delete(id)
    }

    #observeResize(id, target, fn, options = this.defaultResizeOptions) {
        this.#observe({
            id,
            target,
            fn,
            observer: this.#getResizeObserver(),
            observeMethodOptions: options,
        })
    }

    #observeIntersection(id, target, fn, options = this.defaultIntersectionOptions) {
        this.#observe({
            id,
            target,
            fn,
            observer: this.#getIntersectionObserver(options),
            instanceOptions: options,
        })
    }

    #observeMutation(id, target, fn, options = this.defaultMutationOptions) {
        this.#observe({
            id,
            target,
            fn,
            observer: this.#getMutationObserver(),
            observeMethodOptions: options
        })
    }

    resize() {
        return {
            add: (target, fn, options = this.defaultResizeOptions) => {
                this.#observeResize('', target, fn, options)
            },
            addById: (id, target, fn, options = this.defaultResizeOptions) => {
                this.#observeResize(id, target, fn, options)
            },
            remove: (id) => {
                this.#unobserve({ id, observerName: ResizeObserver.name })
            },
            clearElement: (target) => {
                this.clearElement(target, [ResizeObserver.name])
            },
            disconnect: () => {
                this.#getResizeObserver().disconnect()
            }
        }
    }

    intersection() {
        return {
            add: (target, fn, options = this.defaultIntersectionOptions) => {
                this.#observeIntersection('', target, fn, options)
            },
            addById: (id, target, fn, options = this.defaultIntersectionOptions) => {
                this.#observeIntersection(id, target, fn, options)
            },
            remove: (id) => {
                this.#unobserve({ id, observerName: IntersectionObserver.name })
            },
            clearElement: (target) => {
                this.clearElement(target, [IntersectionObserver.name])
            },
            disconnect:() => {
                this.#intersectionObservers.forEach(observer => observer.disconnect())
            }
        }
    }

    mutation() {
        return {
            add: (target, fn, options = this.defaultMutationOptions) => {
                this.#observeMutation('', target, fn, options)
            },
            addById: (id, target, fn, options = this.defaultMutationOptions) => {
                this.#observeMutation(id, target, fn, options)
            },
            remove: (id) => {
                this.#unobserve({ id, observerName: MutationObserver.name })
            },
            clearElement: (target) => {
                this.clearElement(target, [MutationObserver.name])
            },
            disconnect: () => {
                this.#mutationObserver.disconnect()
            }
        }
    }

    clearElement(target, observerNames = [ResizeObserver.name, IntersectionObserver.name, MutationObserver.name]) {
        const element = getElement(target)
        for (let observerName of observerNames) {
            const dataAttr = this.dataSets[observerName] + 'Ids'
            if (element.dataset[dataAttr]) {
                for (let id of element.dataset[dataAttr].split(',')) {
                    if (!id || id === ',') {
                        continue
                    }
                    this.#unobserve({ id, observerName })
                }
            }
        }
    }

    disconnect() {
        this.resize().disconnect()
        this.intersection().disconnect()
        this.mutation().disconnect()
    }

    destroy() {
        this.disconnect()
        this.#observerMap.clear()
        this.#intersectionObservers.clear()
        this.#resizeObserver = null
        this.#mutationObserver = null
    }
}

const ResizeManager = ObserverManager.get('resize')
const IntersectionManager = ObserverManager.get('intersection')
const MutationManager = ObserverManager.get('mutation')

export { ObserverManager, ResizeManager, IntersectionManager, MutationManager }

