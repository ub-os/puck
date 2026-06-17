import Logger from '#/Helper/Logger.js'

// ensure exe.js is only loaded once and log if it is already loaded
if (!window.puckExeLoaded) {
	import('./exe.js').then(data => {
		Logger.console.log('%chead script executed', 'color:orange')
	})
} else {
	Logger.console.warn('puck is already loaded, skipping exe.js')
}