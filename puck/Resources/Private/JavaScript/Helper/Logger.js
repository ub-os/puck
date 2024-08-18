
const disabledConsole = {}
for(let prop in console) {
    if(typeof console[prop] == "function") {
        disabledConsole[prop] = () => {}
    }
}
export default class Logger {
    static enabled = false
    static enable() {
        this.enabled = true
    }
    static disable() {
        this.enabled = false
    }
    static get console() {
        return this.enabled ? console : disabledConsole
    }
}
if (document.getElementById('root').getAttribute('data-logger') === 'enabled') Logger.enable()