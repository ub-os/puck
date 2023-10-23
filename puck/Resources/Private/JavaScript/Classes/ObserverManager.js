import { getElement } from "../General/Functions.js";

class ObserverManager {

    static #instance = null;
    static isInstanced = false;
    static get(observer = '') {
        if (!ObserverManager.#instance) {
            ObserverManager.#instance = new ObserverManager();
            ObserverManager.isInstanced = true;
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

    static get inst() {
        return ObserverManager.get()
    }
    get observerMap() {
        return this.#observerMap
    }

    get mutationObserver() {
        return this.#mutationObserver
    }

    #id = 0
    #observerMap = new Map();
    #intersectionObservers = new Map();
    #resizeObserver = null;
    #mutationObserver = null;
    defaultIntersectionOptions = { root: null, rootMargin: '0px', threshold: 0 };
    defaultMutationOptions = { attributes: true, childList: true, subtree: true };
    dataSets = { ResizeObserver: 'omResiIds', IntersectionObserver: 'omInIds', MutationObserver: 'omMuIds' }
    dataAttrs = { ResizeObserver: 'data-om-resi-ids', IntersectionObserver: 'data-om-in-ids', MutationObserver: 'data-om-mu-ids' }

    constructor() {
        if (ObserverManager.#instance) {
            console.warn('ObserverManager is a singleton. Use "ObserverManager.inst" instead of creating a new instance with "new ObserverManager()".');
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
                        for (let id of entry.target.dataset[this.dataSets.IntersectionObserver].split(',')) {
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
                    for (let id of entry.target.dataset[this.dataSets.ResizeObserver].split(',')) {
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
                console.log('any manager mutation', mutations)
                const dataset = this.dataSets.MutationObserver
                for (let mutation of mutations) {
                    const ids = (mutation.target.dataset[dataset] || mutation.target.closest(`[${this.dataAttrs.MutationObserver}]`).dataset[dataset]).split(',')
                    for (let id of ids) {
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
        const dataAttr = this.dataSets[observer.constructor.name]
        id = id || (this.#id++).toString()
        element.dataset[dataAttr] = (element.dataset[dataAttr] || '').replace(id + ',', '') + id + ','
        this.#observerMap.set(id, { element, instanceOptions, fn })
        if (observeMethodOptions) {
            console.log({observeMethodOptions})
            observer.observe(element, observeMethodOptions)
            const obsNew = new MutationObserver((mutations, observer) => {
                fn(mutations, observer)
            })
            //console.log({ observer, obsNew })
            //obsNew.observe(element, observeMethodOptions)
            console.log(`mutation observing ${id}`, {observer})
        } else {
            observer.observe(element)
        }
    }

    #unobserve({ id, observerName }) {
        if (!this.#observerMap.has(id)) {
            return
        }
        console.log(`unobserving ${observerName} with id ${id}`)
        const obs = this.#observerMap.get(id)
        const dataAttr = this.dataSets[observerName]
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
                this.#getMutationObserver().observe(obs.element, { attribute: true, attributeFilter: [] });
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
        console.log({options})
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
                if (this.#resizeObserver) {
                    this.#resizeObserver.disconnect()
                }
            }
        }
    }

    intersection() {
        return {
            add: (target, fn, options = this.defaultIntersectionOptions) => {
                this.#observeIntersection('', target, fn, options)
            },
            addById: (id, target, fn, options = this.defaultIntersectionOptions) => {
                console.log({options})
                this.#observeIntersection(id, target, fn, options)
            },
            remove: (id) => {
                this.#unobserve({ id, observerName: 'IntersectionObserver' })
            },
            clearElement: (target) => {
                this.clearElement(target, ['IntersectionObserver'])
            },
            disconnect:() => {
                if (this.#intersectionObservers.size) {
                    this.#intersectionObservers.forEach(observer => observer.disconnect())
                }
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
                if (this.#mutationObserver) {
                    this.#mutationObserver.disconnect()
                }
            }
        }
    }

    clearElement(target, observerNames = ['ResizeObserver', 'IntersectionObserver', 'MutationObserver']) {
        const element = getElement(target)
        for (let observerName of observerNames) {
            const dataAttr = this.dataSets[observerName]
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

    #removeAttrsFromElement(element, observerNames = ['ResizeObserver', 'IntersectionObserver', 'MutationObserver']) {
        for (let observerName of observerNames) {
            delete element.dataset[this.dataSets[observerName]]
        }
    }

    disconnect() {
        this.resize().disconnect()
        this.intersection().disconnect()
        this.mutation().disconnect()
    }

    destroy() {
        this.disconnect()
        this.#id = 0
        this.#observerMap.forEach((value, key) => {
            this.#removeAttrsFromElement(value.element)
        })
        this.#observerMap.clear()
    }
}

const ResizeManager = ObserverManager.get('resize')
const IntersectionManager = ObserverManager.get('intersection')
const MutationManager = ObserverManager.get('mutation')

export { ObserverManager, ResizeManager, IntersectionManager, MutationManager }

