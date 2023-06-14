import {$, $$} from "../General/Aliases.js";

export default class App {
    constructor({ ...options }) {
        this.options = { ...options }
        this.components = []
    }
    mount() {
        if (this.options.debug) {
            console.log(this);
        }
        if (this.options.scrollOnCurrentLink) {
            const currentLinks = document.querySelectorAll(`
              a[href="${window.location.href}"], 
              a[href="${window.location.pathname}"], 
              [data-link-to="${window.location.href}"], 
              [data-link-to="${window.location.pathname}"]`)
            currentLinks.forEach(element => {
                element.addEventListener('click', e => {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        left: 0,
                        behavior: 'smooth'
                    });
                })
            })
        }
        return this
    }
}