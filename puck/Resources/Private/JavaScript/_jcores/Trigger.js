import { camelCase } from "./StringUtility"
import config from "./Config"

export default class Trigger {
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
    constructor(el, descriptor, options = {}) {
        this.initialize(el, descriptor, options)
    }

    initialize(el, descriptor, options = {}) {
        this.el = el
        descriptor = this.completeDescriptor(el, descriptor)
        const [
            event,
            methodIdentifier,
            id
        ] = descriptor.split(/->|#/);
        const [
            coreIdentifier,
            coreMethod
        ] = methodIdentifier.split('.')
        const coreEl = id ? document.getElementById(id) : el.closest(`[${config.attributePrefix}${config.coreAttribute}]`)
        const core = coreEl?.['jc_cores']?.get(coreIdentifier)

        if (!coreMethod || !core || typeof core[coreMethod] !== 'function') return
        this.identifier = `${coreIdentifier}-${coreMethod}`
        this.event = event.split('.')[0]
        if (el['jc_triggers']?.get(this.identifier)) {
            console.log(`Trigger ${this.identifier} already connected to ${el.id}`)
            return
        }

        this.setListenerOptions()
        const triggerGuard = this.getTriggerGuard(event)

        this.listener = e => {
            let core = coreEl?.['jc_cores']?.get(coreIdentifier)
            if (triggerGuard(e)) return
            if (!core.__connected) return
            el.hasAttribute(`data-${this.identifier}.event.prevent`) ? e.preventDefault() : null
            el.hasAttribute(`data-${this.identifier}.event.stop`) ? e.stopPropagation() : null
            const params = el.hasAttribute(`data-${this.identifier}`) ? this.typecast(el.getAttribute(`data-${this.identifier}`)) : {}
            e.coreMethodTrigger = {
                core: core,
                method: coreMethod,
            }

            for (const attr of el.attributes) {
                if (attr.name.startsWith(`data-${this.identifier}.`)) {
                    const attrKey = camelCase(attr.name.replace(`data-${this.identifier}.`, ''))
                    params[attrKey] = this.typecast(attr.value)
                }
            }

            core[coreMethod](e, params)
        }

        !el['jc_triggers'] ? el.jc_triggers = new Map() : null
        el.jc_triggers.set(this.identifier, this)
    }

    completeDescriptor(el, descriptor) {
        if (!descriptor.includes('->')) {
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

    setListenerOptions() {
        ['capture', 'once', 'passive'].forEach(opt => {
            const value = this.el.getAttribute(`data-${this.identifier}.event.${opt}`)
            this.listenerOptions[opt] = value === undefined || value === null || value === 'false' || value === '0' ? false : true
        })
    }

    typecast(val) {
        try {
            return JSON.parse(val)
        } catch (e) {
            return val
        }
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