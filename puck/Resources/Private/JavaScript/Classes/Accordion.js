import Toggleable from "./Toggleable"

class Accordion extends Toggleable {
    constructor(target, { useMinHeight = false, ...options }) {
        super(target, { ...options })
        Object.assign(this, { useMinHeight })
    }
    toggleOn(transition= true) {
        super.toggleOn(transition);
        if (this.useMinHeight) {
            this.element.style.minHeight = `${(this.element.scrollHeight).toString()}px`
        } else {
            this.element.style.height = `${(this.element.scrollHeight).toString()}px`
        }
        this.toggles.forEach(t => t.ariaExpanded = 'true')
    }
    toggleOff(transition= true, changeUrlHash =  true) {
        super.toggleOff(transition, changeUrlHash)
        if (this.useMinHeight) {
            this.element.style.minHeight = `0`
        } else {
            this.element.style.height = `0`
        }
        this.toggles.forEach(t => t.ariaExpanded = 'false')
    }
    mount() {
        super.mount()
        return this
    }
}

export default Accordion
