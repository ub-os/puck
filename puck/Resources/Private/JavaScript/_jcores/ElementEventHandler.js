import { camelCase, kebabCase } from "./Utils"
import config from "./Config"

export default class ElementEventHandler {
    /**
     * @type {HTMLElement}
     */
    el = null
    identifier = null
    event = null
    listener = null
    listenerOptions = {}
    eventOptions = {}
    connected = false
    constructor(el, descriptor, eventRegistry = {}) {
        this.initialize(el, descriptor, eventRegistry)
    }

    initialize(el, descriptor, eventRegistry = {}) {
        this.el = el
        descriptor = this.completeDescriptor(el, descriptor)
        const [
            eventDescriptor,
            identifier,
            id
        ] = descriptor.split(/->|#/);

        let event = eventDescriptor
        let optionDescriptor = ''
        if (eventDescriptor.includes('[')) {
            [event, optionDescriptor] = eventDescriptor.replace(']', '').split('[')
            optionDescriptor.split(' ').forEach(option => {
                let value = true
                if (option.startsWith('!')) {
                    option = option.slice(1)
                    value = false
                }
                this.listenerOptions[option] = value
            })
        }
        if (identifier.startsWith('$emit.')) {
            this.initializeEmitHandler(el, event, identifier.split('$emit.')[1], id, eventRegistry)
        } else {
            this.initializeMethodHandler(el, event, identifier, id)
        }
    }
    
    initializeMethodHandler(el, event, identifier, id) {
        const [
            coreIdentifier,
            coreMethod
        ] = identifier.split('.')
        const coreEl = id ? document.getElementById(id) : el.closest(`[${config.attributePrefix}${config.coreAttribute}]`)
        const core = coreEl?.['jc_aspects']?.get(coreIdentifier)

        if (!coreMethod || !core || typeof core[coreMethod] !== 'function') return
        this.identifier = kebabCase(`${coreIdentifier}.${coreMethod}`)
        this.event = event.split('.')[0]
        if (el['jc_handlers']?.get(this.identifier)) {
            console.log(`Handler ${this.identifier} already connected to ${el.id}`)
            return
        }

        const triggerGuard = this.getTriggerGuard(event)

        this.listener = e => {
            let core = coreEl?.['jc_aspects']?.get(coreIdentifier)
            if (triggerGuard(e)) return
            if (!core.__connected) return
            if (this.listenerOptions.prevent) {
                e.preventDefault()
            }
            if (this.listenerOptions.stop) {
                e.stopPropagation()
            }

            const params = el.hasAttribute(`${config.attributePrefix}${this.identifier}`) ? this.typecast(el.getAttribute(`${config.attributePrefix}${this.identifier}`)) : {}
            for (const attr of el.attributes) {
                if (attr.name.startsWith(`${config.attributePrefix}${this.identifier}.`)) {
                    const attrKey = camelCase(attr.name.replace(`${config.attributePrefix}${this.identifier}.`, ''))
                    params[attrKey] = this.typecast(attr.value)
                }
            }
            e.handlerTarget = el

            core[coreMethod](params, e)
        }

        !el['jc_handlers'] ? el.jc_handlers = new Map() : null
        el.jc_handlers.set(this.identifier, this)
    }

    initializeEmitHandler(el, event, eventToEmit, id = '', eventRegistry) {
        if (!eventToEmit) return
        const targetEl = id ? document.getElementById(id) : document.body
        this.identifier = eventToEmit
        this.eventOptions = eventRegistry[eventToEmit] ?? {}
        this.event = event.split('.')[0]
        if (el['jc_handlers']?.get(this.identifier)) {
            console.log(`Handler $emit ${this.identifier} already connected to ${el.id}`)
            return
        }

        const triggerGuard = this.getTriggerGuard(event)

        this.listener = e => {
            if (triggerGuard(e)) return
            if (this.listenerOptions.prevent) {
                e.preventDefault()
            }
            if (this.listenerOptions.stop) {
                e.stopPropagation()
            }
            const eventOptions = this.eventOptions
            eventOptions.detail = {
                ...eventOptions.detail ?? {},
                ...el.hasAttribute(`${config.attributePrefix}${this.identifier}`) ? this.typecast(el.getAttribute(`${config.attributePrefix}${this.identifier}`)) : {},
                originalEvent: e
            }

            for (const attr of el.attributes) {
                if (attr.name.startsWith(`${config.attributePrefix}${this.identifier}.`)) {
                    const attrKey = camelCase(attr.name.replace(`${config.attributePrefix}${this.identifier}.`, ''))
                    if (attrKey === 'originalEvent') return
                    eventOptions.detail[attrKey] = this.typecast(attr.value)
                }
            }

            targetEl.dispatchEvent(new CustomEvent(eventToEmit, eventOptions))
        }

        !el['jc_handlers'] ? el.jc_handlers = new Map() : null
        el.jc_handlers.set(this.identifier, this)
    }

    completeDescriptor(el, descriptor) {
        if (descriptor.includes('->')) {
            return descriptor
        }
        if (el.tagName === 'FORM') {
            descriptor = `submit->${descriptor}`
        } else if (el.tagName === 'INPUT' && el.type !== 'submit') {
            descriptor = `input->${descriptor}`
        } else if (el.tagName === 'TEXTAREA') {
            descriptor = `input->${descriptor}`
        } else if (el.tagName === 'SELECT') {
            descriptor = `change->${descriptor}`
        } else if (el.tagName === 'DETAILS') {
            descriptor = `toggle->${descriptor}`
        } else {
            descriptor = `click->${descriptor}`
        }
        return descriptor
    }

    getTriggerGuard(eventDescriptor) {
        if (eventDescriptor.startsWith('keydown') || eventDescriptor.startsWith('keyup')) {
            let key, modifier
            if (eventDescriptor.includes('+')) {
                key = eventDescriptor.split('+')[1]?.toLowerCase()
                modifier = eventDescriptor.split('.')[1].split('+')[0]?.toLowerCase()
            } else {
                key = eventDescriptor.split('.')[1]?.toLowerCase()
            }
            return e => {
                if (key && e.key.toLowerCase() !== key) return true
                if (modifier && !e[`${modifier}Key`]) return true
                return false
            }
        }
        return e => false
    }

    typecast(val) {
        try { return JSON.parse(val) } catch { return val }
    }

    connect() {
        if (this.connected) return
        this.el.addEventListener(this.event, this.listener, this.listenerOptions)
        this.connected = true
    }

    disconnect() {
        if (!this.connected) return
        this.el.removeEventListener(this.event, this.listener, this.listenerOptions)
        this.connected = false
    }
}