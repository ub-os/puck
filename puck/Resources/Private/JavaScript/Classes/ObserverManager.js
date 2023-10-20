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
        const element = getElement(target)
        const dataAttr = this.dataSets[observer.constructor.name] + 'Ids'
        id = id || (this.#id++).toString()
        element.dataset[dataAttr] = ( element.dataset[dataAttr] || '' ) + id + ','
        this.#observerMap.set(id, { element, instanceOptions, fn })
        if (observeMethodOptions) {
            observer.observe(element, observeMethodOptions)
        } else {
            observer.observe(element)
        }
    }

    #unobserve({ id, observerName }) {
        if (!this.#observerMap.has(id)) {
            return
        }
        const obs = this.#observerMap.get(id)
        const dataAttr = this.dataSets[observerName] + 'Ids'
        console.log({observerName, dataset: this.dataSets, dataAttr})
        console.log(obs.element.dataset)
        obs.element.dataset[dataAttr] = obs.element.dataset[dataAttr].replace(id + ',', '')
        if (obs.element.dataset[dataAttr] === '' || obs.element.dataset[dataAttr] === ',') {
            delete obs.element.dataset[dataAttr]
            if (observerName === 'IntersectionObserver') {
                this.#getIntersectionObserver(obs.instanceOptions).unobserve(obs.element)
                if (!this.#getIntersectionObserver(obs.instanceOptions).takeRecords().length) {
                    this.#intersectionObservers.delete(JSON.stringify(obs.instanceOptions))
                }
            }
            if (observerName === 'ResizeObserver') {
                this.#getResizeObserver().unobserve(obs.element)
            }
            if (observerName === 'MutationObserver') {
                //
            }
        }
        this.#observerMap.delete(id)
    }

    #observeResize(id, target, fn) {
        this.#observe({
            id,
            target,
            fn,
            observer: this.#getResizeObserver(),
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
            add: (target, fn) => {
                this.#observeResize('', target, fn)
            },
            addById: (id, target, fn) => {
                this.#observeResize(id, target, fn)
            },
            remove: (id) => {
                this.#unobserve({ id, observerName: 'ResizeObserver' })
            },
            clearElement: (target) => {
                this.clearElement(target, ['ResizeObserver'])
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
                this.#unobserve({ id, observerName: 'IntersectionObserver' })
            },
            clearElement: (target) => {
                this.clearElement(target, ['IntersectionObserver'])
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
                this.#unobserve({ id, observerName: 'MutationObserver' })
            },
            clearElement: (target) => {
                this.clearElement(target, ['MutationObserver'])
            },
            disconnect: () => {
                this.#mutationObserver.disconnect()
            }
        }
    }

    clearElement(target, observerNames) {
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

