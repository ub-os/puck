import Showable from "~/Aspects/Showable"


export default class Accordion extends Showable {
    static displayName = 'Accordion'
    static attributes = {
        ...Showable.attributes,
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
    show({ transition = true } = {}) {
        super.show({ transition })
        window.requestAnimationFrame(() => {
            this.el.style[this.useMinHeight ? 'minHeight' : 'height'] = `${(this.el.scrollHeight).toString()}px`
        })
        this.toggleElements.forEach(t => t.ariaExpanded = 'true')
    }
    hide({ transition = true, changeUrlHash = true } = {}) {
        super.hide({ transition, changeUrlHash })
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


