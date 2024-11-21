import Showable from "~/Aspects/Showable"

export default class Accordion extends Showable {
    static attributes = {
        ...Showable.attributes,
        useMinHeight: false,
        pauseMediaOnHide: true,
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
    onShow(event) {
        super.onShow(event)
        this.setShowHeight()
        this.toggleElements.forEach(t => t.ariaExpanded = 'true')
    }
    onHide(event) {
        super.onHide(event)
        this.setHideHeight()
        // hacky way to enable exit transition, otherwise content instantly disappears
        if (event.detail.transition) {
            this.el.setAttribute('open', '')
            setTimeout(() => {
                this.el.removeAttribute('open')
            }, this.duration)
        } else {
            this.el.removeAttribute('open')
        }
        this.toggleElements.forEach(t => t.ariaExpanded = 'false')
    }
    setShowHeight() {
        this.el.style[this.useMinHeight ? 'minHeight' : 'height'] = `${(this.el.scrollHeight).toString()}px`
    }
    setHideHeight() {
        this.el.style[this.useMinHeight ? 'minHeight' : 'height'] = `${this.el.$('summary')?.offsetHeight ?? '0'}px`
    }
    connected() {
        super.connected()
        this.active ? this.setShowHeight() : this.setHideHeight()
        return this
    }
    disconnected() {
        super.disconnected()
    }
}


