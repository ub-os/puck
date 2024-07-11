import Toggleable from "~/Cores/Toggleable"


export default class Accordion extends Toggleable {
    static displayName = 'Accordion'
    static attributes = {
        ...Toggleable.attributes,
        useMinHeight: false,
    }
    toggleUnitConnected(el) {
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
        this.el.setAttribute('open', '')
        this.toggleUnits.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.el.style[this.useMinHeight ? 'minHeight' : 'height'] = `${this.el.$('summary')?.offsetHeight ?? '0'}px`
        window.requestAnimationFrame(() => {
            this.el.removeAttribute('open')
        })
        this.toggleUnits.forEach(t => t.ariaExpanded = 'false')
    }
    connect() {
        super.connect()
        return this
    }
    disconnect() {
        super.disconnect()
    }
}


