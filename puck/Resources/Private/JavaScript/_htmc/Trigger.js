import { camelCase } from "./Utility/StringUtility"
import { $id } from "./Utility/DomUtility"
import AttributeConverter from "./Service/AttributeConverter"

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
    constructor(el, descriptor, eventRegistry = {}) {
        if (descriptor.includes('::')) this.initializeCoreMethod(el, descriptor, eventRegistry)
        else this.initializeEvent(el, descriptor, eventRegistry)
    }

    initializeEvent(el, descriptor, eventRegistry) {
        this.el = el
        descriptor = this.completeDescriptor(el, descriptor)
        const [
            event,
            triggeredEvent,
            id
        ] = descriptor.split(/->|#/);
        const targetEl = id ? $id(id) : document.body
        if (!triggeredEvent) return

        this.identifier = `${triggeredEvent}_`
        this.eventOptions = eventRegistry[this.identifier] ?? {}
        this.event = event.split('.')[0]
        if (el['htmcEvents']?.get(this.identifier)) {
            console.log(`Event ${this.identifier} already connected to ${el.id}`)
            return
        }

        this.setListenerOptions()
        const triggerGuard = this.getTriggerGuard(event)
        const attributeConverter = new AttributeConverter(this.eventOptions.detail ?? {})
        const datasetIdentifier = camelCase(this.identifier) + ':'

        this.listener = e => {
            if (triggerGuard(e)) return
            el.hasAttribute(`data-${this.identifier}:event:prevent`) ? e.preventDefault() : null
            el.hasAttribute(`data-${this.identifier}:event:stop`) ? e.stopPropagation() : null
            const eventOptions = {
                ...this.eventOptions,
                detail: {
                    ...this.eventOptions.detail ?? {},
                    triggerEvent: e
                }
            }

            for (const attr of el.attributes) {
                if (attr.name.startsWith(`data-${this.identifier}:`)) {
                    const attrKey = camelCase(attr.name.replace(`data-${this.identifier}:`, ''))
                    if (attrKey === 'triggerEvent') return
                    eventOptions.detail[attrKey] = attributeConverter.read(attrKey, attr.value)
                }
            }
/*
            Object.entries({...el.dataset}).forEach(([key, value]) => {
                if (key.startsWith(datasetIdentifier)) {
                    const attrKey = key.replace(datasetIdentifier, '')
                    if (attrKey === 'triggerEvent') return
                    eventOptions.detail[attrKey] = attributeConverter.read(attrKey, value)
                }
            })*/
            console.log(eventOptions)
            targetEl.dispatchEvent(new CustomEvent(triggeredEvent, eventOptions))
        }

        !el['htmcEvents'] ? el.htmcEvents = new Map() : null
        el.htmcEvents.set(this.identifier, this)
    }

    initializeCoreMethod(el, descriptor, eventRegistry) {
        this.el = el
        descriptor = this.completeDescriptor(el, descriptor)
        const [
            event,
            coreIdentifier,
            coreMethod,
            id
        ] = descriptor.split(/->|::|#/);
        const coreEl = id ? $id(id) : el.closest(`[data-core]`)
        const core = coreEl?.['htmcCores']?.get(coreIdentifier)

        if (!coreMethod || !core || typeof core[coreMethod] !== 'function') return
        this.identifier = `${coreIdentifier}::${coreMethod}`
        this.event = event.split('.')[0]
        if (el['htmcTriggers']?.get(this.identifier)) {
            console.log(`Trigger ${this.identifier} already connected to ${el.id}`)
            return
        }

        this.setListenerOptions()
        const triggerGuard = this.getTriggerGuard(event)
        const attributeConverter = new AttributeConverter(core.constructor.triggerables?.[coreMethod] || {})
        const datasetIdentifier = camelCase(this.identifier) + ':'

        this.listener = e => {
            if (triggerGuard(e)) return
            if (!core.__connected) return
            el.hasAttribute(`data-${this.identifier}:event:prevent`) ? e.preventDefault() : null
            el.hasAttribute(`data-${this.identifier}:event:stop`) ? e.stopPropagation() : null
            const params = attributeConverter.attributes
            e.coreMethodTrigger = {
                core: core,
                method: coreMethod,
            }

            for (const attr of el.attributes) {
                if (attr.name.startsWith(`data-${this.identifier}:`)) {
                    const attrKey = camelCase(attr.name.replace(`data-${this.identifier}:`, ''))
                    if (attrKey === 'triggerEvent') return
                    params[attrKey] = attributeConverter.read(attrKey, attr.value)
                }
            }

/*            Object.entries({...el.dataset}).forEach(([key, value]) => {
                if (key.startsWith(datasetIdentifier)) {
                    const attrKey = key.replace(datasetIdentifier, '')
                    params[attrKey] = attributeConverter.read(attrKey, value)
                }
            })*/

            core[coreMethod](e, params)
        }

        !el['htmcTriggers'] ? el.htmcTriggers = new Map() : null
        el.htmcTriggers.set(this.identifier, this)
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
            //const value = this.el.dataset[`${this.identifier}:event:${opt}`]
            const value = this.el.getAttribute(`data-${this.identifier}:event:${opt}`)
            this.listenerOptions[opt] = value === undefined || value === null || value === 'false' || value === '0' ? false : true
        })
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