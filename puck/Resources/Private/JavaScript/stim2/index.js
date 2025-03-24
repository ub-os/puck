const kebabCase = str => str.replace(/[A-Z]+(?![a-z])|[A-Z]/g, ($, ofs) => (ofs ? "-" : "") + $.toLowerCase())
const camelCase = str => str.replace(/-([a-z])/g, (_, c) => c.toUpperCase());

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
	props = {}
	keys = {}
	token = ''
	get propsAttr() {
		return `${config.attributePrefix}${this.token}`
	}
	constructor(props = {}, token = '') {
		this.props = props
		this.token = token
		for (const key in this.props) {
			const attr = `${this.propsAttr}.${kebabCase(key)}`
			this.keys[key] = attr
			this.keys[attr] = key
		}
	}
	write(key, val) {
		if (typeof this.props[key] == 'string') return val
		if (typeof this.props[key] == 'boolean') return val ? '' : 'false'
		try { return JSON.stringify(val) } catch { return val }
	}
	read(key, val) {
		if (typeof this.props[key] == 'string') return val
		if (typeof this.props[key] == 'boolean') return val !== '0' && val !== 'false'
		try { return JSON.parse(val) } catch { return val }
	}
	init(object, element) {
		const attrProps = JSON.parse(element.getAttribute(this.propsAttr) || '{}')
		for (const key in this.props) {
			const attr = this.keys[key]
			if (element.hasAttribute(attr)) {
				this.set(object, element, key, this.read(key, element.getAttribute(attr)), false)
			} else if (key in attrProps) {
				this.set(object, element, key, attrProps[key], true)
			} else {
				this.set(object, element, key, this.props[key], false)
			}
		}
		element.removeAttribute(this.propsAttr)
	}
	set(object, element, key, val, sync = true) {
		if (val === object[`#${key}`]) return
		const oldVal = object[`#${key}`]
		object[`#${key}`] = val
		if (typeof object[`${key}PropChanged`] === 'function') {
			object[`${key}PropChanged`](oldVal, val)
		}
		if (!sync) return
		const defaultVal = this.props[key]
		const writeVal = this.write(key, val)
		const attr = this.keys[key]
		if (val === defaultVal || (typeof defaultVal === 'object' && writeVal === this.write(key, defaultVal))) {
			element.removeAttribute(attr)
			return
		}
		element.setAttribute(attr, writeVal)
	}
	attributeChanged(object, element, name, oldVal, newVal) {
		if (name === config.propsAttr) {
			this.init(object, element)
			return
		}
		const key = this.keys[name]
		if (!key) {
			object.attributeChanged(name, oldVal, newVal)
			return
		}
		const newReadVal = (newVal === null || newVal === undefined) ? this.props[key] : this.read(key, newVal)
		this.set(object, element, key, newReadVal, false)
	}
}

class Stim {
	config = config
	traitRegister = {}
	selectorRegister = {}
	injectTokens = {}
	syncers = {}
	orphans = new Map([['', new Set()]])
	traits = new WeakMap()
	refs = new WeakMap()
	handlers = new WeakMap()
	/** @internal trait attribute name */
	get tAtt() {
		return config.attributePrefix + config.traitAttribute
	}
	/** @internal ref attribute name */
	get rAtt() {
		return config.attributePrefix + config.refAttribute
	}
	/** @internal handler attribute name */
	get hAtt() {
		return config.attributePrefix + config.handlerAttribute
	}
	obs = {
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
				const el = mutation.target
				if (mutation.attributeName === this.tAtt) {
					this.setTraits(el, el.getAttribute(this.tAtt))
				} else if (mutation.attributeName === this.rAtt) {
					this.setRefs(el, el.getAttribute(this.rAtt))
				} else if (mutation.attributeName === this.hAtt) {
					this.setHandlers(el, el.getAttribute(this.hAtt))
				}
			})
		}),
		trait: new MutationObserver(mutations => {
			mutations.forEach(mutation => {
				if (!mutation.attributeName.startsWith(config.attributePrefix)) return
				const el = mutation.target
				const newVal = el.getAttribute(mutation.attributeName)
				if (mutation.oldValue == newVal) return
				for (const token in this.traits.get(el)) {
					this.syncers[token].attributeChanged(this.traits.get(el)[token], el, mutation.attributeName, mutation.oldValue, newVal)
				}
			})
		})
	}
	connect() {
		this.connectElement(document.documentElement)
		if (config.observeChildList) {
			this.obs.childList.observe(document.documentElement, { childList: true, subtree: true })
		}
		if (config.observeAttributes) {
			this.obs.attribute.observe(document.documentElement, { attributes: true, attributeOldValue: true, attributeFilter: [this.tAtt, this.rAtt, this.hAtt], subtree: true })
		}
	}
	disconnect() {
		for (const observer of this.obs) {
			observer.takeRecords()
			observer.disconnect()
		}
		this.disconnectElement(document.documentElement)
	}
	connectElement(el) {
		if (el.nodeType !== Node.ELEMENT_NODE) return
		for (const selector in this.selectorRegister) {
			if (el.matches(selector)) {
				this.selectorRegister[selector](el)
			}
			el.querySelectorAll(selector).forEach(el => this.selectorRegister[selector](el))
		}
		if (el.hasAttribute(this.tAtt)) this.setTraits(el, el.getAttribute(this.tAtt))
		el.querySelectorAll(`[${this.tAtt}]`).forEach(target => this.setTraits(target, target.getAttribute(this.tAtt)))
		if (el.hasAttribute(this.rAtt)) this.setRefs(el, el.getAttribute(this.rAtt))
		el.querySelectorAll(`[${this.rAtt}]`).forEach(target => this.setRefs(target, target.getAttribute(this.rAtt)))
		if (el.hasAttribute(this.hAtt)) this.setHandlers(el, el.getAttribute(this.hAtt))
		el.querySelectorAll(`[${this.hAtt}]`).forEach(target => this.setHandlers(target, target.getAttribute(this.hAtt)))
	}
	disconnectElement(el) {
		if (el.nodeType !== Node.ELEMENT_NODE) return
		if (el.hasAttribute(this.hAtt)) this.setHandlers(el)
		el.querySelectorAll(`[${this.hAtt}]`).forEach(target => this.setHandlers(target))
		if (el.hasAttribute(this.rAtt)) this.setRefs(el)
		el.querySelectorAll(`[${this.rAtt}]`).forEach(target => this.setRefs(target))
		if (el.hasAttribute(this.tAtt)) this.setTraits(el)
		el.querySelectorAll(`[${this.tAtt}]`).forEach(target => this.setTraits(target))
	}
	setTraits(el, tokens = null) {
		const traits = this.traits.get(el) ?? {}
		const addTokens = new Set(tokens?.split(' ').flatMap(token => this.injectTokens[token] ?? []))
		const removeTokens = new Set(Object.keys(traits))
		for (const syncToken of addTokens) {
			const token = syncToken.split('/')[0]
			removeTokens.delete(token)
			if (!traits[token]) {
				this.addTrait(el, syncToken)
			}
		}
		for (const token in removeTokens) {
			this.removeTrait(traits[token])
		}
	}
	setRefs(el, descriptors = null) {
		const refs = this.refs.get(el) ?? {}
		const descriptorSet = new Set(descriptors?.split(' '))
		for (const descriptor in refs) {
			if (!descriptorSet.has(descriptor)) {
				this.removeRef(refs[descriptor])
			}
		}
		for (const descriptor of descriptorSet) {
			if (!refs[descriptor]) {
				this.addRef(this.refs.get(el)?.[descriptor] || new TraitRef(el, descriptor))
			}
		}
	}
	setHandlers(el, descriptors = null) {
		const handlers = this.handlers.get(el) ?? {}
		const descriptorSet = new Set(descriptors?.split(' '))
		for (const descriptor in handlers) {
			if (!descriptorSet.has(descriptor)) {
				this.removeHandler(handlers[descriptor])
			}
		}
		for (const descriptor of descriptorSet) {
			if (!handlers[descriptor]) {
				this.addHandler(this.handlers.get(el)?.[descriptor] || new TraitHandler(el, descriptor))
			}
		}
	}
	addTrait(el, syncToken) {
		if (!this.traits.get(el)) this.traits.set(el, {})
		const [token, injectorToken] = syncToken.split('/')
		const trait = this.traits.get(el)[token] = this.traits.get(el)[token] || new this.traitRegister[token](el, syncToken)
		if (!stim.syncers[syncToken]) {
			stim.syncers[syncToken] = new PropSyncer(
				{...this.traitRegister[token].props, ... this.traitRegister[injectorToken]?.traits[token] ?? {}},
				token
			)
		}
		stim.syncers[syncToken].init(trait, el)
		if (config.observeTraitAttributes) this.obs.trait.observe(el, {attributes: true, attributeOldValue: true})
		trait.connected()
		if (el.id && this.orphans.has(el.id)) {
			for (const ref of this.orphans.get(el.id)) {
				if (ref.token == token) {
					this.addRef(ref)
				}
			}
		}
		for (const ref of this.orphans.get('')) {
			if (el.contains(ref.el) && ref.token == token) {
				this.addRef(ref)
			}
		}
	}
	removeTrait(trait) {
		trait.disconnected()
		for (const type in trait.$refs) {
			for (const ref of trait.$refs[type]) {
				this.removeRef(ref, true)
			}
		}
	}
	addRef(ref) {
		if (ref.trait) {
			return
		}
		const target = ref.targetId ? document.getElementById(ref.targetId) : ref.el.closest(`[${this.tAtt}]`)
		ref.trait = this.traits.get(target)?.[ref.token]
		if (!ref.trait) {
			ref.trait = null
			this.addOrphan(ref)
			return
		}
		this.removeOrphan(ref)
		ref.trait.$refs[ref.type].add(ref.el)
		if (!this.refs.get(ref.el)) this.refs.set(ref.el, {})
		this.refs.get(ref.el)[ref.descriptor] = ref
		const callbackName = `${camelCase(ref.type)}RefConnected`
		if (typeof ref.trait[callbackName] == 'function') {
			ref.trait[callbackName](ref.el)
		}
	}
	removeRef(ref, addOrphan = false) {
		if (!addOrphan) this.removeOrphan(ref)
		if (!ref.trait) {
			return
		}
		if (addOrphan) this.addOrphan(ref)
		ref.trait.$refs[ref.type].delete(ref.el)
		delete this.refs.get(ref.el)[ref.descriptor]
		const callbackName = `${camelCase(ref.type)}RefDisconnected`
		if (typeof ref.trait[callbackName] == 'function') {
			ref.trait[callbackName](ref.el)
		}
		ref.trait = null
	}
	addHandler(handler) {
		if (handler.listener) {
			return
		}
		handler.listener = event => {
			const target = handler.targetId ? document.getElementById(handler.targetId) : handler.el.closest(`[${this.tAtt}]`)
			const trait = this.traits.get(target)?.[handler.token]
			if (!trait) return
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
		if (!this.handlers.get(handler.el)) this.handlers.set(handler.el, {})
		this.handlers.get(handler.el)[handler.descriptor] = handler
		handler.el.addEventListener(handler.event, handler.listener, handler.options)
	}
	removeHandler(handler) {
		if (!handler.listener) {
			return
		}
		handler.el.removeEventListener(handler.event, handler.listener, handler.options)
		delete this.handlers.get(handler.el)[handler.descriptor]
		handler.listener = null
	}
	addOrphan(ref) {
		if (!ref.targetId) {
			this.orphans.get('').add(ref)
			return
		}
		if (!this.orphans.has(ref.targetId)) {
			this.orphans.set(ref.targetId, new Set())
		}
		this.orphans.get(ref.targetId).add(ref)
	}
	removeOrphan(ref) {
		if (ref.targetId) {
			this.orphans.get(ref.targetId)?.delete(ref)
			if (this.orphans.get(ref.targetId)?.size == 0) {
				this.orphans.delete(ref.targetId)
			}
		} else {
			this.orphans.get('').delete(ref)
		}
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
					return this[`#${key}`]
				},
				set(val) {
					self.syncers[this.$sync].set(this, this.$el, key, val, true)
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
		this.injectTokens[token] = [token]
		for (const injectToken in traitClass.traits) {
			Object.defineProperty(traitClass.prototype, `${camelCase(injectToken)}Trait`, {
				get() {
					return self.traits.get(this.$el)?.[injectToken]
				},
			})
			this.injectTokens[token].push(`${injectToken}/${token}`)
		}
		this.traitRegister[token] = traitClass
	}
	registerSelectorCallback(selector, callback) {
		if (typeof selector === 'object') {
			for (const [key, value] of Object.entries(selector)) {
				this.registerSelectorCallback(key, value)
			}
			return
		}
		this.selectorRegister[selector] = callback
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
	constructor(el, syncToken) {
		this.$el = el
		this.$sync = syncToken
		this.initialized()
	}
	get token() {
		return this.constructor.token
	}
	get stim() {
		return stim
	}
	get el() {
		return this.$el
	}
	initialized() {}
	connected() {}
	disconnected() {}
	attributeChanged(name, oldValue, newValue) {}
}

// removing properties from TraitRef and TraitHandler: 0.03kb
// renaming traitAttr etc to tAtt etc: 0.02kb

//export { stim, ElementTrait }
