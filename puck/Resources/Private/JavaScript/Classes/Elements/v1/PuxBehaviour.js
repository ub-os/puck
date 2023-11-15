import { getElement, getAllPropertyNames, extendClass } from '../../../General/Utility';
import PuxElement from "./PuxElement.js";
import PuxMixin from "./PuxMixin.js";

// don't use constructor, use mount instead
// define all props in static props, they will be added to the object as this[propName]
export default class PuxBehavior {
    static props = {
    }
    static get Mixin() {
        const className = this.name + 'Mixin'
        if (window[className] !== undefined) return window[className]
        window[className] = class extends PuxMixin {
            static name = className
        }
        extendClass(
            window[className],
            this,
            ['name', 'prototype', 'length', 'Mixin', 'Element', 'registerAsMixin', 'registerAsElement'],
            ['constructor']
        )
        console.dir(window[className])
        return window[className]
    }
    static get Element() {
        const className = this.name + 'Element'
        if (window[className] !== undefined) return window[className]
        window[className] = class extends PuxElement {
            static name = className
        }
        extendClass(
            window[className],
            this,
            ['name', 'prototype', 'length', 'Mixin', 'Element', 'registerAsMixin', 'registerAsElement'],
            ['constructor', 'el']
        )
        console.dir(window[className])
        return window[className]
    }
    static registerAsMixin(attrName) {
        PuxElement.registerMixin(attrName, this.Mixin)
        return this.Mixin
    }
    static registerAsElement(tagName) {
        window.customElements.define('pux-' + tagName, this.Element)
        return this.Element
    }
    static __id = 0
    /**
     * @type {HTMLElement}
     * @private
     */
    __el = null
    get el() { return this.__el }
    mount() {
        console.log(this.constructor.name + ' behavior mount inheritance')
        if (!this.el.id) {
            this.el.id = 'pux-auto-id-' + PuxBehavior.__id++
        }
        return this
    }
    destroy() {
        console.log(this.constructor.name + ' behavior destroy inheritance')
    }
}




