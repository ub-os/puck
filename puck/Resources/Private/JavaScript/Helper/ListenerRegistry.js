/**
 * Represents a single event listener with weak reference to its target.
 * @class
 */
class EventListener {
	#targetRef

	/**
	 * Creates a new EventListener and attaches it to the target.
	 * @param {EventTarget} target - The element to attach the listener to
	 * @param {string} type - The event type (e.g., 'click', 'mouseover')
	 * @param {Function} listener - The event handler function
	 * @param {AddEventListenerOptions} [options={}] - Event listener options
	 */
	constructor(target, type, listener, options = {}) {
		this.#targetRef = new WeakRef(target)
		this.type = type
		this.listener = listener
		this.options = options
		target.addEventListener(this.type, this.listener, this.options)
		Object.freeze(this.options)
		Object.freeze(this)
	}

	/**
	 * Gets the target element, or undefined if it has been garbage collected.
	 * @returns {EventTarget|undefined}
	 */
	get target() {
		return this.#targetRef.deref()
	}

	/**
	 * Removes the event listener from its target.
	 * If the target has been garbage collected, this is a no-op.
	 */
	remove() {
		const target = this.#targetRef.deref()
		if (target) {
			target.removeEventListener(this.type, this.listener, this.options)
		}
	}
}

/**
 * A set of event listeners for managing multiple listeners on potentially multiple targets.
 * @class
 * @example
 * const set = new EventListenerSet()
 * set.add(button, 'click', handleClick)
 * set.add(input, 'input', handleInput)
 * set.clear() // Removes all listeners
 */
class EventListenerSet {
	#set = new Set()

	/**
	 * Creates a new EventListenerSet, optionally initialized with listeners.
	 * @param {Iterable} [iterable=[]] - Initial listeners as [target, type, listener, options] arrays
	 */
	constructor(iterable = []) {
		for (const value of iterable) {
			this.add(...value)
		}
	}

	/**
	 * Adds an event listener to a target and tracks it in this set.
	 * @param {EventTarget} target - The element to attach the listener to
	 * @param {string} type - The event type (e.g., 'click', 'mouseover')
	 * @param {Function} listener - The event handler function
	 * @param {AddEventListenerOptions} [options={}] - Event listener options
	 * @returns {EventListener} The created EventListener instance
	 * @throws {TypeError} If target is not an EventTarget
	 */
	add(target, type, listener, options = {}) {
		if (typeof target?.addEventListener !== 'function') {
			throw new TypeError("parameter 1 is not of type 'EventTarget'")
		}
		const eventListener = new EventListener(target, type, listener, options)
		this.#set.add(eventListener)
		return eventListener
	}

	/**
	 * Adds a delegated event listener using event delegation pattern.
	 * The listener only fires when the event target matches the selector.
	 * @param {EventTarget} target - The element to attach the listener to (usually a parent)
	 * @param {string} selector - CSS selector for delegation (e.g., '.button')
	 * @param {string} type - The event type
	 * @param {Function} listener - The event handler function
	 * @param {AddEventListenerOptions} [options={}] - Event listener options
	 * @returns {EventListener} The created EventListener instance
	 * @example
	 * set.delegate(container, '.delete-btn', 'click', (e) => {
	 *   console.log('Clicked element:', e.delegateTarget)
	 * })
	 */
	delegate(target, selector, type, listener, options = {}) {
		return this.add(target, type, e => (e.delegateTarget = e.target.closest(selector)) && listener(e), options)
	}

	/**
	 * Removes an event listener from the set and from its target.
	 * @param {EventListener} eventListener - The EventListener to remove
	 * @returns {boolean} True if the listener was in the set and removed
	 */
	remove(eventListener) {
		eventListener.remove()
		return this.#set.delete(eventListener)
	}

	/**
	 * Checks if an event listener is in this set.
	 * @param {EventListener} eventListener - The EventListener to check
	 * @returns {boolean}
	 */
	has(eventListener) {
		return this.#set.has(eventListener)
	}

	/**
	 * Removes all event listeners from their targets and clears the set.
	 */
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

	/**
	 * Executes a callback for each listener in the set.
	 * @param {Function} callback - Function to execute for each listener
	 */
	forEach(callback) {
		for (const eventListener of this) {
			callback(eventListener)
		}
	}

	/**
	 * Returns all listeners as an array.
	 * @returns {EventListener[]}
	 */
	get array() {
		return [...this]
	}

	/**
	 * Returns the number of listeners in the set.
	 * @returns {number}
	 */
	get size() {
		return this.#set.size
	}
}

/**
 * A registry for managing event listeners across multiple targets.
 * Provides automatic AbortController integration for fast cleanup and supports external AbortSignals.
 *
 * @class
 * @example
 * const registry = new ListenerRegistry()
 *
 * // Add listeners
 * registry.add(button, 'click', handleClick)
 * registry.add(input, 'input', handleInput)
 *
 * // Remove all listeners for a target (fast path using AbortController)
 * registry.abortTarget(button)
 *
 * // Remove all listeners (fast path)
 * registry.abort()
 *
 * // Using external AbortController
 * const controller = new AbortController()
 * registry.add(button, 'click', handler, { signal: controller.signal })
 * controller.abort() // Removes listener and cleans up registry
 *
 * // Event delegation
 * registry.delegate(container, '.item', 'click', handleItemClick)
 */
class ListenerRegistry {
	#map = new Map()
	#controllers = new WeakMap()

	/**
	 * Creates a new ListenerRegistry, optionally initialized with listeners.
	 * @param {Iterable} [iterable=[]] - Initial listeners as [target, type, listener, options] arrays
	 */
	constructor(iterable = []) {
		for (const value of iterable) {
			this.add(...value)
		}
	}

	/**
	 * Adds an event listener to a target and tracks it in this registry.
	 * Automatically creates an internal AbortController for the target if needed.
	 * Supports external AbortSignals via options.signal.
	 *
	 * @param {EventTarget} target - The element to attach the listener to
	 * @param {string} type - The event type (e.g., 'click', 'mouseover')
	 * @param {Function} listener - The event handler function
	 * @param {AddEventListenerOptions} [options={}] - Event listener options (can include signal)
	 * @returns {EventListener} The created EventListener instance
	 * @throws {TypeError} If target is not an EventTarget
	 * @example
	 * // Basic usage
	 * registry.add(button, 'click', () => console.log('clicked'))
	 *
	 * // With external AbortController
	 * const controller = new AbortController()
	 * registry.add(button, 'click', handler, { signal: controller.signal })
	 * controller.abort() // Auto-removes from registry
	 *
	 * // With options
	 * registry.add(button, 'click', handler, { once: true, passive: true })
	 */
	add(target, type, listener, options = {}) {
		// Initialize target if needed
		if (!this.#map.has(target)) {
			this.#map.set(target, new EventListenerSet())
			// Create internal AbortController for fast cleanup
			this.#controllers.set(target, new AbortController())
		}

		const controller = this.#controllers.get(target)
		const finalOptions = { ...options }

		// If user provided an external signal, handle both signals
		if (options.signal) {
			const userSignal = options.signal
			const internalSignal = controller.signal

			// Create a combined signal that aborts when either signal aborts
			if (typeof AbortSignal.any === 'function') {
				// Modern approach (Chrome 116+, Firefox 115+)
				finalOptions.signal = AbortSignal.any([userSignal, internalSignal])
			} else {
				// Fallback: use user's signal and listen to internal signal
				const abortListener = () => {
					// Can't remove listener if already aborted, just let it be
				}
				internalSignal.addEventListener('abort', abortListener)
				// Keep user's signal in options
			}
		} else {
			// No external signal, use internal one
			finalOptions.signal = controller.signal
		}

		const eventListener = this.#map.get(target).add(target, type, listener, finalOptions)

		// If user provided a signal, clean up registry when it aborts
		if (options.signal) {
			const cleanup = () => {
				this.remove(eventListener)
			}

			if (options.signal.aborted) {
				cleanup()
			} else {
				options.signal.addEventListener('abort', cleanup, { once: true })
			}
		}

		return eventListener
	}

	/**
	 * Adds a delegated event listener using event delegation pattern.
	 * The listener only fires when the event target matches the selector.
	 *
	 * @param {EventTarget} target - The element to attach the listener to (usually a parent)
	 * @param {string} selector - CSS selector for delegation (e.g., '.button')
	 * @param {string} type - The event type
	 * @param {Function} listener - The event handler function (receives event with delegateTarget property)
	 * @param {AddEventListenerOptions} [options={}] - Event listener options
	 * @returns {EventListener} The created EventListener instance
	 * @example
	 * registry.delegate(list, 'li', 'click', (e) => {
	 *   console.log('Clicked item:', e.delegateTarget.textContent)
	 * })
	 */
	delegate(target, selector, type, listener, options = {}) {
		return this.add(target, type, e => {
			const delegateTarget = e.target.closest(selector)
			if (delegateTarget) {
				e.delegateTarget = delegateTarget
				listener(e)
			}
		}, options)
	}

	/**
	 * Removes an event listener from the registry and from its target.
	 * @param {EventListener} eventListener - The EventListener to remove
	 * @returns {boolean} True if the listener was in the registry and removed
	 */
	remove(eventListener) {
		const target = eventListener.target
		if (!target) return false

		const result = this.#map.get(target)?.remove(eventListener)
		if (this.#map.get(target)?.size === 0) {
			this.#map.delete(target)
		}
		return result
	}

	/**
	 * Checks if an event listener is in this registry.
	 * @param {EventListener} eventListener - The EventListener to check
	 * @returns {boolean}
	 */
	has(eventListener) {
		const target = eventListener.target
		if (!target) return false
		return this.#map.get(target)?.has(eventListener)
	}

	/**
	 * Checks if a target has any listeners in this registry.
	 * @param {EventTarget} target - The target to check
	 * @returns {boolean}
	 */
	hasTarget(target) {
		return this.#map.has(target)
	}

	/**
	 * Gets the EventListenerSet for a specific target.
	 * @param {EventTarget} target - The target
	 * @returns {EventListenerSet|undefined} The set of listeners for this target
	 */
	getTarget(target) {
		return this.#map.get(target)
	}

	/**
	 * Gets the internal AbortController for a specific target.
	 * Useful for manual control or inspection.
	 * @param {EventTarget} target - The target
	 * @returns {AbortController|undefined} The controller for this target
	 * @example
	 * const controller = registry.getController(button)
	 * controller.abort() // Same as registry.abortTarget(button)
	 */
	getController(target) {
		return this.#controllers.get(target)
	}

	/**
	 * Removes all listeners for a target by calling removeEventListener for each.
	 * For faster cleanup, use abortTarget() instead.
	 * @param {EventTarget} target - The target to clear
	 * @returns {boolean} True if the target existed and was cleared
	 */
	clearTarget(target) {
		this.#map.get(target)?.clear()
		return this.#map.delete(target)
	}

	/**
	 * Fast path: Aborts all listeners for a target using its AbortController.
	 * This is much faster than clearTarget() for targets with many listeners.
	 * @param {EventTarget} target - The target to abort listeners for
	 * @returns {boolean} True if the target existed and was aborted
	 * @example
	 * registry.add(button, 'click', handler1)
	 * registry.add(button, 'mouseover', handler2)
	 * registry.abortTarget(button) // Removes both listeners instantly
	 */
	abortTarget(target) {
		const controller = this.#controllers.get(target)
		if (!controller) return false

		controller.abort()
		this.#map.delete(target)
		return true
	}

	/**
	 * Removes all listeners from all targets by calling removeEventListener for each.
	 * For faster cleanup, use abort() instead.
	 */
	clear() {
		for (const set of this.#map.values()) {
			set.clear()
		}
		this.#map.clear()
	}

	/**
	 * Fast path: Aborts all listeners for all targets using AbortControllers.
	 * This is much faster than clear() when there are many listeners.
	 * @example
	 * registry.abort() // Instantly removes all listeners
	 */
	abort() {
		for (const [target] of this.#map) {
			this.#controllers.get(target)?.abort()
		}
		this.#map.clear()
	}

	[Symbol.iterator]() {
		return this.#map[Symbol.iterator]()
	}

	/**
	 * Returns an iterator of [target, EventListenerSet] entries.
	 * @returns {Iterator<[EventTarget, EventListenerSet]>}
	 */
	entries() {
		return this.#map.entries()
	}

	/**
	 * Returns an iterator of all targets with listeners.
	 * @returns {Iterator<EventTarget>}
	 */
	targets() {
		return this.#map.keys()
	}

	/**
	 * Returns an iterator of all EventListenerSets.
	 * @returns {Iterator<EventListenerSet>}
	 */
	sets() {
		return this.#map.values()
	}

	/**
	 * Returns an iterator of all EventListener instances across all targets.
	 * @returns {Iterator<EventListener>}
	 */
	*listeners() {
		for (const set of this.#map.values()) {
			yield* set
		}
	}

	/**
	 * Executes a callback for each target and its listener set.
	 * @param {Function} callback - Function to execute: (target, set, registry) => void
	 */
	forEach(callback) {
		for (const [target, set] of this) {
			callback(target, set, this)
		}
	}

	/**
	 * Returns all [target, set] entries as an array.
	 * @returns {Array<[EventTarget, EventListenerSet]>}
	 */
	get array() {
		return [...this]
	}

	/**
	 * Returns the number of targets with listeners.
	 * @returns {number}
	 */
	get size() {
		return this.#map.size
	}
}

export { EventListener, EventListenerSet, ListenerRegistry }