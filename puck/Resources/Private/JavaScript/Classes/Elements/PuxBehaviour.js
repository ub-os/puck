import { getElement } from '../../General/Utility';
import PuxElement from "./PuxElement.js";
import PuxMixin from "./PuxMixin.js";

export default class PuxBehavior {
    static props = {
    }
    constructor() {}
    setProps(props) {
        Object.assign(this, props)
        return this
    }
    mount() {
        return this
    }
    destroy() {}

    static get MixinClass() {
        const className = this.name + 'Mixin'
        if (window[className] === undefined) {
            window[className] = class extends PuxMixin {
                static name = className
                constructor(el, props) {
                    super(el, props);
                }
            };
            for (let key in this) {
                if (key === 'props') {
                    window[className].props = {
                        ...PuxMixin.props,
                        ...this.props
                    }

                } else if (key !== 'prototype' && key !== 'name' ) {
                    window[className][key] = this[key]
                }
            }
            Object.getOwnPropertyNames(this.prototype)
                .filter(prop => prop != 'constructor')
                .forEach(prop => window[className].prototype[prop] = this.prototype[prop])
        }
        return window[className]
    }

    static get ElementClass() {
        const className = this.name + 'Element'
        if (window[className] === undefined) {
            window[className] = class extends PuxElement {
                static name = className
                constructor() {
                    super();
                }
            };
            for (let key in this) {
                if (key === 'props') {
                    window[className].props = {
                        ...PuxElement.props,
                        ...this.props
                    }
                    continue
                }
                if (key !== 'prototype' && key !== 'name' ) {
                    window[className][key] = this[key]
                }
            }
            Object.getOwnPropertyNames(this.prototype)
                .filter(prop => prop != 'constructor')
                .forEach(prop => window[className].prototype[prop] = this.prototype[prop])
        }
        return window[className]
    }

    static registerAsMixin(attrName) {
        PuxElement.registerMixin(attrName, this.MixinClass)
        return this.MixinClass
    }

    static registerAsElement(tagName) {
        window.customElements.define('pux-' + tagName, this.ElementClass)
        return this.ElementClass
    }
}



