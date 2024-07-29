import Trigger from "./Trigger"
import { camelCase } from "./StringUtility"

export default class EventTrigger extends Trigger {

    constructor(el, descriptor, eventRegistry) {
        super(el, descriptor, eventRegistry)
    }
    initialize(el, descriptor, eventRegistry) {
        this.el = el
        descriptor = this.completeDescriptor(el, descriptor)
        const [
            event,
            triggeredEvent,
            id
        ] = descriptor.split(/->|#/);
        const targetEl = id ? document.getElementById(id) : document.body
        if (!triggeredEvent) return

        this.identifier = `${triggeredEvent}_`
        this.event = event.split('.')[0]
        if (el['jc_triggers']?.get(this.identifier)) {
            console.log(`Event ${this.identifier} already connected to ${el.id}`)
            return
        }

        this.setListenerOptions()
        const triggerGuard = this.getTriggerGuard(event)

        this.listener = e => {
            if (triggerGuard(e)) return
            el.hasAttribute(`data-${this.identifier}.event.prevent`) ? e.preventDefault() : null
            el.hasAttribute(`data-${this.identifier}.event.stop`) ? e.stopPropagation() : null

            const eventOptions = this.eventOptions
            eventOptions.detail = {
                ...eventOptions.detail ?? {},
                ...el.hasAttribute(`data-${this.identifier}`) ? this.typecast(el.getAttribute(`data-${this.identifier}`)) : {},
                originalEvent: e
            }

            for (const attr of el.attributes) {
                if (attr.name.startsWith(`data-${this.identifier}.`)) {
                    const attrKey = camelCase(attr.name.replace(`data-${this.identifier}.`, ''))
                    if (attrKey === 'originalEvent') return
                    eventOptions.detail[attrKey] = this.typecast(attr.value)
                }
            }

            targetEl.dispatchEvent(new CustomEvent(triggeredEvent, eventOptions))
        }

        !el['jc_triggers'] ? el.jc_triggers = new Map() : null
        el.jc_triggers.set(this.identifier, this)
    }
}