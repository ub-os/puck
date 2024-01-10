import { camelCase } from "./Utility/StringUtility"
import { $id } from "./Utility/DomUtility"
import AttributeConverter from "./Service/AttributeConverter"

export default class Event {
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

    initialize(el, descriptor, eventRegistry) {
        this.el = el
        descriptor = this.completeDescriptor(el, descriptor)
        const [
            event,
            triggeredEvent,
            id
        ] = descriptor.split(/->|#/);
        const targetEl = id ? $id(id) : document.body
        if (!triggeredEvent) return

        this.identifier = triggeredEvent
        this.eventOptions = eventRegistry[this.identifier] ?? {}
        this.event = event.split('.')[0]
        if (el['stimEvents']?.get(this.identifier)) {
            console.log(`Event ${this.identifier} already connected to ${el.id}`)
            return
        }

        this.setListenerOptions()
        const triggerGuard = this.getTriggerGuard(event)
        const attributeConverter = new AttributeConverter(this.eventOptions.detail ?? {})
        const datasetIdentifier = camelCase(this.identifier) + '_:'

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
            Object.entries({...el.dataset}).forEach(([key, value]) => {
                if (key.startsWith(datasetIdentifier)) {
                    const attrKey = key.replace(datasetIdentifier, '')
                    if (attrKey === 'triggerEvent') return
                    eventOptions.detail[attrKey] = attributeConverter.read(attrKey, value)
                }
            })
            targetEl.dispatchEvent(new CustomEvent(this.identifier, eventOptions))
        }

        !el['stimEvents'] ? el.stimEvents = new Map() : null
        el.stimEvents.set(this.identifier, this)
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
            const value = this.el.dataset[`${this.identifier}:event:${opt}`]
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