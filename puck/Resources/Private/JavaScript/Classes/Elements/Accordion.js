import Toggleable from "./Toggleable"

export default class Accordion extends Toggleable {
    static displayName = 'Accordion'
    static props = {
        ...Toggleable.props,
        useMinHeight: false,
    }
    constructor() {
        super()
        this.heightPropName = this.useMinHeight ? 'minHeight' : 'height'
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        this.style[this.heightPropName] = `${(this.scrollHeight).toString()}px`
        this.toggles.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.style[this.heightPropName] = `0`
        this.toggles.forEach(t => t.ariaExpanded = 'false')
    }
    mount() {
        super.mount()
        return this
    }
    destroy() {
        super.destroy()
    }
}

window.customElements.define('pux-accordion', Accordion);