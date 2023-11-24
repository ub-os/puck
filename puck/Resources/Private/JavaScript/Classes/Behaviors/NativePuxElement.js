import { kebabCase, jsonParse } from "~/General/Utility";
import PuxElement from "~/Classes/Behaviors/PuxElement";

export default class NativePuxElement {
    mixins = {}
    /**
     * @type {HTMLElement}
     * @private
     */
    el = null
    constructor(el) {
        this.el = el
        this.initMixins()
    }
    initMixins() {
        for (let name in PuxElement.mixins) {
            if (!this.el.hasAttribute('data-use-' + name)) continue
            const data = jsonParse(this.el.getAttribute('data-use-' + name))
            this.mixins[name] = new PuxElement.mixins[name](this.el, data)
        }
    }
    mountMixins() {
        for (let mixinName in this.mixins) {
            this.mixins[mixinName].mount()
            console.log('native mixin mounted ' + mixinName)
        }
    }
    destroyMixins() {
        for (let mixinName in this.mixins) {
            this.mixins[mixinName].destroy()
        }
    }
    mount() {
        this.mountMixins()
        return this
    }
    destroy() {
        this.destroyMixins()
    }
}