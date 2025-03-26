const kebabCase = str => str.replace(/[A-Z]+(?![a-z])|[A-Z]/g, ($, ofs) => (ofs ? "-" : "") + $.toLowerCase())
const camelCase = str => str.replace(/-([a-z])/g, (_, c) => c.toUpperCase())

const config = {
	observeChildList: true,
	observeAttributes: true,
	observeTraitAttributes: true,
	attributePrefix: 'data-',
	traitAttribute: 'trait',
	refAttribute: 'ref',
	handlerAttribute: 'handler',
}

class PropSyncer {
	#props = {}
	#keys = {}
	#token = ''
	get #propsAttr() {
		return `${config.attributePrefix}${this.#token}`
	}
	constructor(props, token) {
		this.#props = props
		this.#token = token
		for (const key in this.#props) {
			const attr = `${this.#propsAttr}.${kebabCase(key)}`
			this.#keys[key] = attr
			this.#keys[attr] = key
		}
	}
	#write(key, val) {
		if (typeof this.#props[key] == 'string') return val
		if (typeof this.#props[key] == 'boolean') return val ? '' : 'false'
		try { return JSON.stringify(val) } catch { return val }
	}
	#read(key, val) {
		if (typeof this.#props[key] == 'string') return val
		if (typeof this.#props[key] == 'boolean') return val !== '0' && val !== 'false'
		try { return JSON.parse(val) } catch { return val }
	}
	init(object, element) {
		const attrProps = JSON.parse(element.getAttribute(this.#propsAttr) || '{}')
		for (const key in this.#props) {
			const attr = this.#keys[key]
			if (element.hasAttribute(attr)) {
				this.set(object, element, key, this.#read(key, element.getAttribute(attr)), false)
			} else if (key in attrProps) {
				this.set(object, element, key, attrProps[key], true)
			} else {
				this.set(object, element, key, this.#props[key], false)
			}
		}
		element.removeAttribute(this.#propsAttr)
	}
	set(object, element, key, val, sync = true) {
		if (val === object[`_${key}`]) return
		const oldVal = object[`_${key}`]
		object[`_${key}`] = val
		if (typeof object[`${key}PropChanged`] === 'function') {
			object[`${key}PropChanged`](oldVal, val)
		}
		if (!sync) return
		const defaultVal = this.#props[key]
		const writeVal = this.#write(key, val)
		const attr = this.#keys[key]
		if (val === defaultVal || (typeof defaultVal === 'object' && writeVal === this.#write(key, defaultVal))) {
			element.removeAttribute(attr)
			return
		}
		element.setAttribute(attr, writeVal)
	}
	attributeChanged(object, element, name, oldVal, newVal) {
		if (name === this.#propsAttr) {
			this.init(object, element)
			return
		}
		const key = this.#keys[name]
		if (!key) {
			object.attributeChanged(name, oldVal, newVal)
			return
		}
		const newReadVal = (newVal === null || newVal === undefined) ? this.#props[key] : this.#read(key, newVal)
		this.set(object, element, key, newReadVal, false)
	}
}

class Stim {
	#traitRegister = {}
	#selectorRegister = {}
	#injectTokens = {}
	#syncers = {}
	#orphans = new Map([['', new Set()]])
	#traits = new WeakMap()
	#refs = new WeakMap()
	#handlers = new WeakMap()
	get #traitAttr() {
		return config.attributePrefix + config.traitAttribute
	}
	get #refAttr() {
		return config.attributePrefix + config.refAttribute
	}
	get #handlerAttr() {
		return config.attributePrefix + config.handlerAttribute
	}
	config = config
	get syncers() {
		return this.#syncers
	}
	get traits() {
		return this.#traits
	}
	get refs() {
		return this.#refs
	}
	get handlers() {
		return this.#handlers
	}
	get traitRegister() {
		return this.#traitRegister
	}
	get selectorRegister() {
		return this.#selectorRegister
	}
	#observer = {
		childList: new MutationObserver(mutations => {
			mutations.forEach(mutation => {
				mutation.removedNodes.forEach(node => {
					this.disconnectElement(node)
				})
				mutation.addedNodes.forEach(node => {
					this.connectElement(node)
				})
			})
		}),
		attribute: new MutationObserver(mutations => {
			mutations.forEach(mutation => {
				// disable changing traits, refs, handlers attributes?
				if (mutation.target.isConnected && [this.#traitAttr, this.#refAttr, this.#handlerAttr].includes(mutation.attributeName)) {
					mutation.target.setAttribute(mutation.attributeName, mutation.oldValue)
				}
				// const el = mutation.target
				// if (mutation.attributeName === this.#traitAttr) {
				// 	this.#setTraits(el, el.getAttribute(this.#traitAttr))
				// } else if (mutation.attributeName === this.#refAttr) {
				// 	this.#setRefs(el, el.getAttribute(this.#refAttr))
				// } else if (mutation.attributeName === this.#handlerAttr) {
				// 	this.#setHandlers(el, el.getAttribute(this.#handlerAttr))
				// }
			})
		}),
		trait: new MutationObserver(mutations => {
			mutations.forEach(mutation => {
				if (!mutation.attributeName.startsWith(config.attributePrefix)) return
				const el = mutation.target
				const newVal = el.getAttribute(mutation.attributeName)
				if (mutation.oldValue == newVal) return
				for (const token in this.#traits.get(el)) {
					this.#syncers[token].attributeChanged(this.#traits.get(el)[token], el, mutation.attributeName, mutation.oldValue, newVal)
				}
			})
		})
	}
	connect() {
		if (config.observeChildList) {
			this.#observer.childList.observe(document.documentElement, { childList: true, subtree: true })
		}
		if (config.observeAttributes) {
			this.#observer.attribute.observe(document.documentElement, { attributes: true, attributeOldValue: true, attributeFilter: [this.#traitAttr, this.#refAttr, this.#handlerAttr], subtree: true })
		}
		this.connectElement(document.documentElement)
	}
	disconnect() {
		for (const observer of this.#observer) {
			observer.takeRecords()
			observer.disconnect()
		}
		this.disconnectElement(document.documentElement)
	}
	connectElement(el) {
		if (el.nodeType !== Node.ELEMENT_NODE) return
		for (const selector in this.#selectorRegister) {
			if (el.matches(selector)) {
				this.#selectorRegister[selector](el)
			}
			for (const target of el.querySelectorAll(selector)) this.#selectorRegister[selector](target)
		}
		this.#updateSubtreeConnections(el)
	}
	disconnectElement(el) {
		this.#updateSubtreeConnections(el, true)
	}
	#updateSubtreeConnections(rootEl, removeAll = false) {
		if (rootEl.nodeType !== Node.ELEMENT_NODE) return
		const targets = [rootEl, ...rootEl.querySelectorAll(`[${this.#traitAttr}],[${this.#refAttr}],[${this.#handlerAttr}]`)]
		const handlers = [], refs = [], traits = []
		for (const target of targets) {
			target.hasAttribute(this.#traitAttr) && traits.push(target)
			target.hasAttribute(this.#refAttr) && refs.push(target)
			target.hasAttribute(this.#handlerAttr) && handlers.push(target)
		}
		for (const target of traits) this.#setTraits(target, removeAll ? null : target.getAttribute(this.#traitAttr))
		for (const target of refs) this.#setRefs(target, removeAll ? null : target.getAttribute(this.#refAttr))
		for (const target of handlers) this.#setHandlers(target, removeAll ? null : target.getAttribute(this.#handlerAttr))
	}
	#setTraits(el, tokens = null) {
		const traits = this.#traits.get(el) ?? {}
		const addTokens = new Set(tokens?.split(' ').flatMap(token => this.#injectTokens[token] ?? []))
		const removeTokens = new Set(Object.keys(traits))
		for (const propToken of addTokens) {
			const token = propToken.split('/')[0]
			removeTokens.delete(token)
			this.#addTrait(el, propToken)
		}
		for (const token of removeTokens) {
			this.#removeTrait(traits[token])
		}
	}
	#addTrait(el, propToken) {
		if (!this.#traits.get(el)) this.#traits.set(el, {})
		const [token, injectorToken] = propToken.split('/')
		let trait = this.#traits.get(el)[token]
		if (!trait) {
			trait = this.#traits.get(el)[token] = new this.#traitRegister[token](el, propToken)
			if (!this.#syncers[propToken]) {
				this.#syncers[propToken] = new PropSyncer(
					{...this.#traitRegister[token].props, ...this.#traitRegister[injectorToken]?.traits[token] ?? {}},
					token
				)
			}
			this.#syncers[propToken].init(trait, el)
			trait.initialized()
		}
		if (config.observeTraitAttributes) this.#observer.trait.observe(el, {attributes: true, attributeOldValue: true})
		this.#connectInstance(trait, () => {
			trait.connected()
			if (el.id && this.#orphans.has(el.id)) {
				for (const ref of this.#orphans.get(el.id)) {
					if (ref.token == token) {
						this.#addRef(ref)
					}
				}
			}
			return true
			// for (const ref of this.#orphans.get('')) {
			// 	if (el.contains(ref.el) && ref.token == token) {
			// 		this.#addRef(ref)
			// 	}
			// }
		})
	}
	#removeTrait(trait) {
		this.#disconnectInstance(trait, () => {
			trait.disconnected()
			for (const type in trait.$refs) {
				for (const ref of trait.$refs[type]) {
					this.#removeRef(ref, true)
				}
			}
		})
	}
	#setRefs(el, descriptors = null) {
		const refs = this.#refs.get(el) ?? {}
		const descriptorSet = new Set(descriptors?.split(' '))
		for (const descriptor in refs) {
			if (!descriptorSet.has(descriptor)) {
				this.#removeRef(refs[descriptor])
			}
		}
		for (const descriptor of descriptorSet) {
			if (!refs[descriptor]) {
				this.#addRef(this.#refs.get(el)?.[descriptor] || new TraitRef(el, descriptor))
			}
		}
	}
	#addRef(ref) {
		// if (ref.trait) {
		// 	return
		// }
		this.#connectInstance(ref, () => {
			const target = ref.targetId ? document.getElementById(ref.targetId) : ref.el.closest(`[${this.#traitAttr}]`)
			ref.trait = this.#traits.get(target)?.[ref.token]
			if (!ref.trait) {
				this.#addOrphan(ref)
				return false
			}
			this.#removeOrphan(ref)
			ref.trait.$refs[ref.type].add(ref.el)
			if (!this.#refs.get(ref.el)) this.#refs.set(ref.el, {})
			this.#refs.get(ref.el)[ref.descriptor] = ref
			const callbackName = `${camelCase(ref.type)}RefConnected`
			if (typeof ref.trait[callbackName] == 'function') {
				ref.trait[callbackName](ref.el)
			}
			return true
		})
	}
	#removeRef(ref, addOrphan = false) {
		// if (!addOrphan) this.#removeOrphan(ref)
		// if (!ref.trait) {
		// 	return
		// }
		// if (addOrphan) this.#addOrphan(ref)

		this.#disconnectInstance(ref, () => {
			addOrphan ? this.#addOrphan(ref) : this.#removeOrphan(ref)
			ref.trait.$refs[ref.type].delete(ref.el)
			//delete this.#refs.get(ref.el)[ref.descriptor]
			const callbackName = `${camelCase(ref.type)}RefDisconnected`
			if (typeof ref.trait[callbackName] == 'function') {
				ref.trait[callbackName](ref.el)
			}
		})

		//ref.trait = null
	}
	#setHandlers(el, descriptors = null) {
		const handlers = this.#handlers.get(el) ?? {}
		const descriptorSet = new Set(descriptors?.split(' '))
		for (const descriptor in handlers) {
			if (!descriptorSet.has(descriptor)) {
				this.#removeHandler(handlers[descriptor])
			}
		}
		for (const descriptor of descriptorSet) {
			if (!handlers[descriptor]) {
				this.#addHandler(this.#handlers.get(el)?.[descriptor] || new TraitHandler(el, descriptor))
			}
		}
	}
	#addHandler(handler) {
		// if (handler.listener) {
		// 	return
		// }
		this.#connectInstance(handler, () => {
			handler.listener = event => {
				const target = handler.targetId ? document.getElementById(handler.targetId) : handler.el.closest(`[${this.#traitAttr}]`)
				const trait = this.#traits.get(target)?.[handler.token]
				if (!trait?._$connected) return
				if (handler.options.prevent) event.preventDefault()
				if (handler.options.stop) event.stopPropagation()
				const paramAttr = `${config.attributePrefix + handler.token}.${handler.method}`
				const params = JSON.parse(handler.el.getAttribute(paramAttr) || '{}')
				for (const attr of handler.el.attributes) {
					if (attr.name.startsWith(`${paramAttr}.`)) {
						params[camelCase(attr.name.replace(`${paramAttr}.`, ''))] = JSON.parse(attr.value)
					}
				}
				trait[camelCase(handler.method)]?.(params, event)
			}
			if (!this.#handlers.get(handler.el)) this.#handlers.set(handler.el, {})
			this.#handlers.get(handler.el)[handler.descriptor] = handler
			handler.el.addEventListener(handler.event, handler.listener, handler.options)
		})
	}
	#removeHandler(handler) {
		// if (!handler.listener) {
		// 	return
		// }
		this.#disconnectInstance(handler, () => handler.el.removeEventListener(handler.event, handler.listener, handler.options))
		// delete this.#handlers.get(handler.el)[handler.descriptor]
		// handler.listener = null
	}
	#connectInstance(instance, callback) {
		instance._$connecting = true
		queueMicrotask(() => {
			if (instance._$connected || !instance._$connecting) return
			instance._$connecting = false
			instance._$connected = callback()
		})
	}
	#disconnectInstance(instance, callback) {
		instance._$connecting = false
		if (!instance._$connected) return
		instance._$connected = false
		callback()
	}
	#addOrphan(ref) {
		if (!ref.targetId) {
			//this.#orphans.get('').add(ref)
			return
		}
		if (!this.#orphans.has(ref.targetId)) {
			this.#orphans.set(ref.targetId, new Set())
		}
		this.#orphans.get(ref.targetId).add(ref)
	}
	#removeOrphan(ref) {
		if (ref.targetId) {
			this.#orphans.get(ref.targetId)?.delete(ref)
			if (this.#orphans.get(ref.targetId)?.size == 0) {
				this.#orphans.delete(ref.targetId)
			}
		}
		// else {
		// 	this.#orphans.get('').delete(ref)
		// }
	}
	registerTrait(token, traitClass) {
		if (typeof token === 'object') {
			for (const [key, value] of Object.entries(token)) {
				this.registerTrait(kebabCase(key), value)
			}
			return
		}
		const self = this
		traitClass.token = token
		for (const key in traitClass.props) {
			Object.defineProperty(traitClass.prototype, key, {
				get() {
					return this[`_${key}`]
				},
				set(val) {
					self.syncers[this.$propToken].set(this, this.$el, key, val, true)
				}
			})
		}
		Object.defineProperty(traitClass.prototype, '$refs', {
			value: Object.fromEntries(traitClass.refs.map(key => [key, new Set()]))
		})
		for (const type of traitClass.refs) {
			const camelCasedType = camelCase(type)
			Object.defineProperty(traitClass.prototype, `${camelCasedType}Refs`, {
				get() {
					return this.$refs[type] ?? []
				},
			})
			Object.defineProperty(traitClass.prototype, `${camelCasedType}Ref`, {
				get() {
					return this.$refs[type]?.values()?.next()?.value
				},
			})
		}
		this.#injectTokens[token] = [token]
		for (const injectToken in traitClass.traits) {
			Object.defineProperty(traitClass.prototype, `${camelCase(injectToken)}Trait`, {
				get() {
					return self.traits.get(this.$el)?.[injectToken]
				},
			})
			this.#injectTokens[token].push(`${injectToken}/${token}`)
		}
		this.#traitRegister[token] = traitClass
		traitClass.registered(token, this)
	}
	registerSelectorCallback(selector, callback) {
		if (typeof selector === 'object') {
			for (const [key, value] of Object.entries(selector)) {
				this.registerSelectorCallback(key, value)
			}
			return
		}
		this.#selectorRegister[selector] = callback
	}
}

const stim = new Stim()

const defaultEvents = {
	'FORM': 'submit',
	'INPUT': 'input',
	'TEXTAREA': 'input',
	'SELECT': 'change',
	'DETAILS': 'toggle',
}

class TraitHandler {
	options = {}
	constructor(el, descriptor) {
		this.descriptor = descriptor
		if (!descriptor.includes('->')) {
			descriptor = `${defaultEvents[el.tagName] ?? 'click'}->${descriptor}`
		}
		let eventDescriptor, optionDescriptor
		[this.el, eventDescriptor, this.token, this.method] = [el, ...descriptor.split(/->|\.|#/, 3)]
		this.targetId = descriptor.includes('#') ? descriptor.slice(descriptor.lastIndexOf('#') + 1) : ''
		;[this.event, optionDescriptor] = eventDescriptor.split('[')
		if (optionDescriptor) {
			optionDescriptor.replace(']', '').split(' ').forEach(option => {
				let value = true
				if (option.startsWith('!')) {
					option = option.slice(1)
					value = false
				}
				this.options[option] = value
			})
		}
	}
}

class TraitRef {
	constructor(el, descriptor) {
		[this.el, this.descriptor, this.token, this.type] = [el, descriptor, ...descriptor.split(/[.#]/, 2)]
		this.targetId = descriptor.includes('#') ? descriptor.slice(descriptor.lastIndexOf('#') + 1) : ''
	}
}

class ElementTrait {
	static token = ''
	static traits = {}
	static props = {}
	static refs = []
	static registered(token, stim) {}
	get token() {
		return this.constructor.token
	}
	get el() {
		return this.$el
	}
	get stim() {
		return stim
	}
	constructor(el, propToken) {
		this.$el = el
		this.$propToken = propToken
	}
	initialized() {}
	connected() {}
	disconnected() {}
	attributeChanged(name, oldValue, newValue) {}
}
// removing properties from TraitRef and TraitHandler: 0.03kb
// renaming traitAttr etc to tAtt etc: 0.02kb

// export { stim, ElementTrait }
