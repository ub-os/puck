import Logger from 'Helper/Logger'
import('./exe').then(data => {
	Logger.console.log('%chead script executed', 'color:orange')
})
