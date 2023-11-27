import Toggleable from "~/Controller/Toggleable"

export default class Accordion extends Toggleable {
    static displayName = 'Accordion'
    static props = {
        ...Toggleable.props,
        useMinHeight: false,
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        this.el.style[this.heightPropName] = `${(this.el.scrollHeight).toString()}px`
        this.toggles.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.el.style[this.heightPropName] = `0`
        this.toggles.forEach(t => t.ariaExpanded = 'false')
    }
    connect() {
        super.connect()
        this.heightPropName = this.useMinHeight ? 'minHeight' : 'height'
        return this
    }
    disconnect() {
        super.disconnect()
    }
}
