import { getElement } from '../../General/Utility';

export default class PuxMixin {
    static props = {}
    constructor(el, props) {
        this.el = el
        for (let key in this.constructor.props) {
            if (props[key] !== undefined) {
                this[key] = props[key]
            }
        }
    }
    #el = null
    set el(el) {
        this.#el = el
    }
    get el() {
        return this.#el
    }
}



