import Plyr from 'plyr';
import AbstractComponent from "./AbstractComponent";

export default class MediaPlayer extends AbstractComponent {
    constructor(target, { ...options }) {
        super(target)
        Object.assign(this, { })
        this.player = new Plyr(this.element, {... options})
    }
    mount() {
        return this
    }

}