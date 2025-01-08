/**
 * <b>SessionParams</b><br>
 * stores url params in session storage
 *
 * - SessionParams.setParams(['foo', 'bar']).on() // stores foo and bar params in session storage and listens to popstate event
 * - SessionParams.get('foo') // returns value of foo param
 * - SessionParams.clearStorage() // clears all stored params
 * - SessionParams.off() // stops listening to popstate event
 * - SessionParams.addParams(['baz']) // adds baz param to stored params
 * - SessionParams.removeParams(['foo']) // removes foo param from stored params
 */
export default class SessionParams {
	static #params = []
	static #removedParams = []
	static sessionItemPrefix = 'puck-url-params-'

	static #storeParams() {
		const urlParams = new URLSearchParams(window.location.search)
		this.#params.forEach(key => {
			const value = urlParams.get(key)
			if (value === null) return
			sessionStorage.setItem(this.sessionItemPrefix + key, value)
		})
	}

	static setParams(...params) {
		this.#params = params
		this.#storeParams()
		return SessionParams
	}

	static addParams(...params) {
		this.#params = [...this.#params, ...params]
		this.#storeParams()
		return SessionParams
	}

	static removeParams(...params) {
		this.#params = this.#params.filter(p => !params.includes(p))
		this.#removedParams = [...this.#removedParams, ...params]
		return SessionParams
	}

	static get(key) {
		return sessionStorage.getItem(this.sessionItemPrefix + key)
	}

	static on() {
		window.addEventListener('popstate', () => {
			this.#storeParams()
		})
		return SessionParams
	}

	static off() {
		window.removeEventListener('popstate', () => {
			this.#storeParams()
		})
		return SessionParams
	}

	static clearStorage() {
		;(this.#params + this.#removedParams).forEach(key => {
			sessionStorage.removeItem(this.sessionItemPrefix + key)
		})
		return SessionParams
	}
}
