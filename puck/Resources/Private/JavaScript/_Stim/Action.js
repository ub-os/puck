import { camelCase } from "./Utility/StringUtility"
import { $id } from "./Utility/DomUtility"
import AttributeConverter from "./Service/AttributeConverter"
import StimEvent from "./Event"

export default class Action extends StimEvent {
    initialize(el, descriptor, eventRegistry) {
        this.el = el
        descriptor = this.completeDescriptor(el, descriptor)
        const [
            event,
            controllerIdentifier,
            controllerMethod,
            id
        ] = descriptor.split(/->|::|#/);
        const controllerEl = id ? $id(id) : el.closest(`[data-controller]`)
        const controller = controllerEl?.['stimControllers']?.get(controllerIdentifier)

        if (!controllerMethod || !controller || typeof controller[controllerMethod] !== 'function') return
        this.identifier = `${controllerIdentifier}::${controllerMethod}`
        this.event = event.split('.')[0]
        if (el['stimActions']?.get(this.identifier)) {
            console.log(`Action ${this.identifier} already connected to ${el.id}`)
            return
        }

        this.setListenerOptions()
        const triggerGuard = this.getTriggerGuard(event)
        const attributeConverter = new AttributeConverter(controller.constructor.actions?.[controllerMethod] || {})
        const datasetIdentifier = camelCase(this.identifier) + ':'

        this.listener = e => {
            if (triggerGuard(e)) return
            if (!controller.__connected) return
            el.hasAttribute(`data-${this.identifier}:event:prevent`) ? e.preventDefault() : null
            el.hasAttribute(`data-${this.identifier}:event:stop`) ? e.stopPropagation() : null
            const params = attributeConverter.attributes
            e.actionTrigger = {
                controller: controller,
                method: controllerMethod,
            }
            Object.entries({...el.dataset}).forEach(([key, value]) => {
                if (key.startsWith(datasetIdentifier)) {
                    const attrKey = key.replace(datasetIdentifier, '')
                    params[attrKey] = attributeConverter.read(attrKey, value)
                }
            })
            controller[controllerMethod](e, params)
        }

        !el['stimActions'] ? el.stimActions = new Map() : null
        el.stimActions.set(this.identifier, this)
    }

}