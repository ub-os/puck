import {$, $$} from "../General/Aliases.js";

export default class App {
    constructor({ ...options }) {
        this.options = { ...options }
        this.components = {}

    }
    mount() {
        document.body.classList.remove('u-no-transition');
        $$('.u-initially-hidden').forEach(node => {
            node.classList.remove('u-initially-hidden');
        });
        if (this.options.debug) {
            console.log(this);
        }
        return this
    }
}