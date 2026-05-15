import Logger from './Helper/Logger.js'
import('./exe.js').then(data => {
	Logger.console.log('%chead script executed', 'color:orange')
})
