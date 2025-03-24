class IterableWeakMap {
	#weakMap = new WeakMap()
	#refSet = new Set()
	#finalizationGroup = new FinalizationRegistry(IterableWeakMap.#cleanup)
	static #cleanup({ set, ref }) {
		set.delete(ref)
	}
	constructor(iterable = []) {
		for (const [key, value] of iterable) {
			this.set(key, value)
		}
	}
	set(key, value) {
		const ref = new WeakRef(key)
		this.#weakMap.set(key, { value, ref })
		this.#refSet.add(ref)
		this.#finalizationGroup.register(
			key,
			{
				set: this.#refSet,
				ref,
			},
			ref,
		)
	}
	get(key) {
		const entry = this.#weakMap.get(key)
		return entry ? entry.value : undefined
	}
	delete(key) {
		const entry = this.#weakMap.get(key)
		if (!entry) {
			return false
		}
		this.#weakMap.delete(key)
		this.#refSet.delete(entry.ref)
		this.#finalizationGroup.unregister(entry.ref)
		return true
	}
	*[Symbol.iterator]() {
		for (const ref of this.#refSet) {
			const key = ref.deref()
			if (!key) continue
			const { value } = this.#weakMap.get(key)
			yield [key, value]
		}
	}
	entries() {
		return this[Symbol.iterator]()
	}
	*keys() {
		for (const [key, value] of this) {
			yield key
		}
	}
	*values() {
		for (const [key, value] of this) {
			yield value
		}
	}
}

class EventListener {
	constructor(target, type, listener, options = {}) {
		this.target = target
		this.type = type
		this.listener = listener
		this.options = options
		this.target.addEventListener(this.type, this.listener, this.options)
		Object.freeze(this.options)
		Object.freeze(this)
	}
	remove() {
		this.target.removeEventListener(this.type, this.listener, this.options)
		return this
	}
}

class EventListenerSet {
	#set = new Set()
	constructor(iterable = []) {
		for (const value of iterable) {
			this.add(...value)
		}
	}
	add(target, type, listener, options = {}) {
		if (typeof target?.addEventListener !== 'function') {
			throw new TypeError("parameter 1 is not of type 'EventTarget'");
		}
		const eventListener = new EventListener(target, type, listener, options)
		this.#set.add(eventListener)
		return eventListener
	}
	addDelegate(target, selector, type, listener, options = {}) {
		return this.add(target, type, e => (e.delegateTarget = e.target.closest(selector)) && listener(e), options)
	}
	remove(eventListener) {
		eventListener.remove()
		return this.#set.delete(eventListener)
	}
	has(eventListener) {
		return this.#set.has(eventListener)
	}
	clear() {
		for (const eventListener of this.#set) {
			eventListener.remove()
		}
		this.#set.clear()
	}
	*[Symbol.iterator]() {
		for (const eventListener of this.#set) {
			yield eventListener
		}
	}
	*entries() {
		for (const eventListener of this) {
			yield [eventListener, eventListener]
		}
	}
	*keys() {
		for (const eventListener of this) {
			yield eventListener
		}
	}
	*values() {
		for (const eventListener of this) {
			yield eventListener
		}
	}
	forEach(callback) {
		for (const eventListener of this) {
			callback(eventListener)
		}
	}
	get array() {
		return [...this]
	}
	get size() {
		return [...this].length
	}
}

class EventListenerRegistry {
	#map = new IterableWeakMap()
	constructor(iterable = []) {
		for (const value of iterable) {
			this.add(...value)
		}
	}
	add(target, type, listener, options = {}) {
		if (!this.#map.get(target)) this.#map.set(target, new EventListenerSet())
		return this.#map.get(target).add(target, type, listener, options)
	}
	addDelegate(target, selector, type, listener, options = {}) {
		return this.add(target, type, e => (e.delegateTarget = e.target.closest(selector)) && listener(e), options)
	}
	remove(eventListener) {
		const result = this.#map.get(eventListener.target)?.remove(eventListener)
		if (this.#map.get(eventListener.target)?.size == 0) {
			this.#map.delete(eventListener.target)
		}
		return result
	}
	has(eventListener) {
		return this.#map.get(eventListener.target)?.has(eventListener)
	}
	hasTarget(target) {
		return this.#map.get(target)
	}
	getTarget(target) {
		return this.#map.get(target)
	}
	clearTarget(target) {
		this.#map.get(target)?.clear()
		return this.#map.delete(target)
	}
	clear() {
		for (const set of this.#map.values()) {
			set.clear()
		}
		this.#map = new IterableWeakMap()
	}
	[Symbol.iterator]() {
		return this.#map[Symbol.iterator]()
	}
	entries() {
		return this.#map.entries()
	}
	targets() {
		return this.#map.keys()
	}
	sets() {
		return this.#map.values()
	}
	*listeners() {
		for (const set of this.#map.values()) {
			yield* set;
		}
	}
	forEach(callback) {
		for (const [target, set] of this) {
			callback(target, set, this)
		}
	}
	get array() {
		return [...this]
	}
	get size() {
		return [...this].length
	}
}

export { EventListener, EventListenerSet, EventListenerRegistry }