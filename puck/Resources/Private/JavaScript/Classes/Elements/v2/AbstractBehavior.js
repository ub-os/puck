import PuxElement from "./PuxElement";

// don't use constructor, use mount instead
// define all props in static props, they will be added to the object as this[propName]
export default class AbstractBehavior {
    static props = { }
    static __id = 0
    static registerAsMixin(attrName) {
        PuxElement.registerMixin(attrName, this)
        return this
    }
    static registerAsElement(tagName) {
        window.customElements.define('pux-' + tagName, this.Element)
        return this.Element
    }
    static get Element() {
        const className = this.name + 'Element'
        if (window[className] !== undefined) return window[className]
        const Behavior = this
        window[className] = class extends PuxElement {
            static name = className
            static core = Behavior
        }
        return window[className]
    }
    constructor(el, props = {}) {
        this.el = el
        if (!this.el.id) {
            this.el.id = 'pux-auto-id-' + AbstractBehavior.__id++
        }
        for (let key in this.constructor.props) {
            if (props[key] !== undefined) {
                this[key] = props[key]
            } else {
                this[key] = this.constructor.props[key]
            }
        }
    }
    set el(el) { this.__el = el }
    get el() { return this.__el }
    /**
     * @type {HTMLElement}
     * @private
     */
    __el = null
    mount() { return this }
    destroy() { }
}




