import PuxElement from "~/Classes/Behaviors/PuxElement.js"

const elementClasses = {}
/**
 * How to use class extending AbstractBehavior:
 * 1. don't override constructor; put all initialization code in mount method
 * 2. define props in static props; they will be added to the object as this[propName] by the constructor
 * 3. define mount method; all functionality, listeners, observers should be added here
 * 4. define destroy method; all listeners and observers should be removed here
 * 5. create instance with new MyBehavior(el, props), with props being an object with a subset of the props defined in static props
 * 6. mount instance with myBehavior.mount()
 * 7. destroy instance with myBehavior.destroy()
 * 8. register as mixin with MyBehavior.registerAsMixin('my-behavior') => mixin can be used on pux elements via use-my-behavior attribute
 * 9. register as html element with MyBehavior.registerAsElement('my-behavior') => element can be used via <pux-my-behavior>my-behavior element</pux-my-behavior>
 */
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
        if (elementClasses[className] !== undefined) return elementClasses[className]
        const Behavior = this
        elementClasses[className] = class extends PuxElement {
            static name = className
            static core = Behavior
        }
        return elementClasses[className]
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




