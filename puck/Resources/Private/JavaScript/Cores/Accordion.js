import Toggleable from "~/Cores/Toggleable"


export default class Accordion extends Toggleable {
    static displayName = 'Accordion'
    static attributes = {
        ...Toggleable.attributes,
        useMinHeight: false,
    }
    toggleElementConnected(el) {
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
        this.toggleElements.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        this.el.style[this.useMinHeight ? 'minHeight' : 'height'] = `${this.el.$('summary')?.offsetHeight ?? '0'}px`
        // hacky way to enable exit transition, otherwise content instantly disappears
        window.requestAnimationFrame(() => {
            if (transition) {
                this.el.setAttribute('open', '')
                setTimeout(() => {
                    this.el.removeAttribute('open')
                }, this.duration)
            } else {
                this.el.removeAttribute('open')
            }
        })
        this.toggleElements.forEach(t => t.ariaExpanded = 'false')
    }
    connect() {
        super.connect()
        return this
    }
    disconnect() {
        super.disconnect()
    }
}


