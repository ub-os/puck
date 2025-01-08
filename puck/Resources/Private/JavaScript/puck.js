import Logger from '~/Helper/Logger'

import('~/exe.js').then(data => {
	Logger.console.log('%chead script executed', 'color:orange')
})
