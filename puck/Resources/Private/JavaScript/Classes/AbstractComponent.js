import {getElement} from '../General/Functions';

export default class AbstractComponent {

    constructor(target, {...options} = {}) {
        this.element = getElement(target, this.constructor.name)
        if (!this.element.id) {
            this.element.id = this.constructor.name.replace(/([a-z0–9])([A-Z])/g, "$1-$2").toLowerCase() + '-' + Math.random().toString(36).substring(2, 9)
        }
        Object.assign(this, options)
        this.id = this.element.id
    }

    mount() {
        return this
    }
    destroy() {}

    static createInstancesFromDataAttribute({
            root = document,
            attribute,
            options = {}
        }) {
        const getOptions = (str, options) => {
            try {
                return {...JSON.parse(str), ...options}
            } catch (e) {
                return str
            }
        }
        const elements = [...root.querySelectorAll(`[${attribute}]`)] || []
        return new Map(elements.map((element, i) => [
                element.id || i,
                new this(
                    element,
                    getOptions(element.getAttribute(`${attribute}`) || '{}', options)
                ).mount()
            ]
        ))
    }
}