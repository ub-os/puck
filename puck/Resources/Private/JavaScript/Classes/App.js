import {$, $$} from "../General/Aliases.js";
import Listeners from "./Listeners.js";

export default class App {
    components = []
    managers = []
    options = {}
    location = window.location
    constructor({ ...options }) {
        this.options = { ...options }
    }
    mount() {
        if (this.options.debug) console.log(this)
        this.listeners = new Listeners()
        this.listeners.add(window, 'popstate', (e) => {
            this.location = window.location
        })
        return this
    }
}