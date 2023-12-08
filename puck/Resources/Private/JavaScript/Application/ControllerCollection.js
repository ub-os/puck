import { kebabCase } from "~/Utility/StringUtility.js";
import { $$ } from "~/Utility/DomUtility.js";

export default class ControllerCollection {
    static controllers = {}
    static #idx = 0
    static processControllerProps(identifier, constructor) {
        constructor.propToAttrName = {}
        for (let prop in constructor.props) {
            constructor.propToAttrName[prop] = `data-${identifier}-${kebabCase(prop)}`
        }
        constructor.attrToPropName = {}
        for (let prop in constructor.props) {
            constructor.attrToPropName[`data-${identifier}-${kebabCase(prop)}`] = prop
        }
        for (let prop in constructor.props) {
            Object.defineProperty(constructor.prototype, prop, {
                get() {
                    return this[`#${prop}`]
                },
                set(val) {
                    this.setProp(prop, val, true)
                }
            })
        }
    }
    static register(identifier, constructor) {
        constructor.identifier = identifier
        this.processControllerProps(identifier, constructor)
        constructor.registerCallback()
        this.controllers[identifier] = constructor
    }
    list = []
    /**
     * @type {HTMLElement}
     */
    el = null
    constructor(el) {
        this.el = el
        if (!this.el.id) {
            this.el.id = `controller-el-${ControllerCollection.#idx++}`
        }
        this.el.dataset.controller?.split(' ').forEach(identifier => {
            if (!ControllerCollection.controllers[identifier]) {
                console.warn(`Controller ${identifier} not found, skipping.`)
                return
            }
            this.list.push(new ControllerCollection.controllers[identifier](this.el))
        })
    }
    addController(identifier) {
        //this.set.add(new ControllerCollection.controllers[identifier](this.el))
        return this
    }
    connectedCallback() {
        this.list.forEach(controller => {
            controller.connect()
        })
        return this
    }
    disconnectedCallback() {
        this.list.forEach(controller => {
            controller.disconnect()
        })
        return this
    }
    attributeChangedCallback(name, oldVal, newVal) {
        if (oldVal === newVal) return this
        this.list.forEach(controller => controller.attributeChanged(name, oldVal, newVal))
        return this
    }
}