class IterableWeakMap {
    #weakMap = new WeakMap();
    #refSet = new Set();
    #finalizationGroup = new FinalizationRegistry(IterableWeakMap.#cleanup);

    static #cleanup({ set, ref }) {
        set.delete(ref);
    }

    constructor(iterable = []) {
        for (const [key, value] of iterable) {
            this.set(key, value);
        }
    }

    set(key, value) {
        const ref = new WeakRef(key);

        this.#weakMap.set(key, { value, ref });
        this.#refSet.add(ref);
        this.#finalizationGroup.register(key, {
            set: this.#refSet,
            ref
        }, ref);
    }

    get(key) {
        const entry = this.#weakMap.get(key);
        return entry && entry.value;
    }

    delete(key) {
        const entry = this.#weakMap.get(key);
        if (!entry) {
            return false;
        }

        this.#weakMap.delete(key);
        this.#refSet.delete(entry.ref);
        this.#finalizationGroup.unregister(entry.ref);
        return true;
    }

    *[Symbol.iterator]() {
        for (const ref of this.#refSet) {
            const key = ref.deref();
            if (!key) continue;
            const { value } = this.#weakMap.get(key);
            yield [key, value];
        }
    }

    entries() {
        return this[Symbol.iterator]();
    }

    *keys() {
        for (const [key, value] of this) {
            yield key;
        }
    }

    *values() {
        for (const [key, value] of this) {
            yield value;
        }
    }
}

export default class EventHandlerSet {
    #map = new IterableWeakMap();

    constructor(iterable = []) {
        for (const value of iterable) {
            this.add(...value);
        }
    }

    add(el, type, callback, options = {}) {
        el.addEventListener(type, callback, options)
        if (this.#map.get(el)) {
            this.#map.get(el).add({ type, callback, options })
        } else {
            this.#map.set(el, new Set([{ type, callback, options }]))
        }
        return { el, type, callback, options }
    }

    addDelegate(el, selector, type, callback, options = {}) {
        const delegateCallback = e => {
            const target = e.target.closest(selector)
            if (target) {
                e.delegateTarget = target
                callback(e)
            }
        }
        return this.add(el, type, delegateCallback, options)
    }

    delete({ el, type, callback, options }) {
        el.removeEventListener(type, callback, options)
        return this.#map.get(el)?.delete({ type, callback, options })
    }

    has({ el, type, callback, options }) {
        return this.#map.get(el)?.has({ type, callback, options })
    }

    get(el) {
        return this.#map.get(el)
    }

    *[Symbol.iterator]() {
        for (const [el, set] of this.#map) {
            for (const { type, callback, options } of set) {
                yield { el, type, callback, options }
            }
        }
    }

    *keys() {
        for (const handler of this) {
            yield handler
        }
    }

    *values() {
        for (const handler of this) {
            yield handler
        }
    }
    
    forEach(cb) {
        for (const { el, type, callback, options } of this) {
            cb({ el, type, callback, options })
        }
    }

    clear() {
        for (const { el, type, callback, options } of this) {
            el.removeEventListener(type, callback, options)
        }
        this.#map = new IterableWeakMap()
    }

    get size() {
        return [...this].length
    }

}