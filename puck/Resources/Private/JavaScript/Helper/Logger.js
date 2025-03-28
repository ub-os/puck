class Logger {
	disabledConsole = {}
	enabled = false
	get console() {
		return this.enabled ? console : this.disabledConsole
	}
	constructor() {
		for (const prop in console) {
			if (typeof console[prop] == 'function') {
				this.disabledConsole[prop] = () => {}
			}
		}
	}
	enable() {
		this.enabled = true
	}
	disable() {
		this.enabled = false
	}
}
const logger = new Logger()
export default logger
if (document.getElementById('root').getAttribute('data-logger') === 'enabled') {
	logger.enable()
}
