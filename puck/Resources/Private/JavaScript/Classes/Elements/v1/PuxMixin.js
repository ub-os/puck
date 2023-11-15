import { getElement } from '../../../General/Utility';

export default class PuxMixin {
    static props = {}
    constructor(el, props) {
        this.el = el
        for (let key in this.constructor.props) {
            if (props[key] !== undefined) {
                this[key] = props[key]
            } else {
                this[key] = this.constructor.props[key]
            }
        }
    }
    __el = null
    set el(el) { this.__el = el }
    get el() { return this.__el }
}


