import Toggleable from "~/Controller/Toggleable"

/**
    * @property {Map} toggleTargets
 */
export default class Accordion extends Toggleable {
    static displayName = 'Accordion'
    static props = {
        ...Toggleable.props,
        useMinHeight: false,
    }
    toggleConnected(el) {
        el.ariaControls = this.el.id
        if (this.active) {
            el.classList.add(this.activeClass)
            el.ariaExpanded = 'true'
        } else {
            el.ariaExpanded = 'false'
        }
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        this.el.style[this.useMinHeight ? 'minHeight' : 'height'] = `${(this.el.scrollHeight).toString()}px`
        this.toggleTargets.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.el.style[this.useMinHeight ? 'minHeight' : 'height'] = `0`
        this.toggleTargets.forEach(t => t.ariaExpanded = 'false')
    }
    connect() {
        super.connect()
        return this
    }
    disconnect() {
        super.disconnect()
    }
}


