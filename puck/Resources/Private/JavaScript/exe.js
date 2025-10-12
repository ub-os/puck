import Logger from '~/Helper/Logger'
import { stim } from '~/setup'

stim.connect()
Logger.console.log(stim)

window.requestAnimationFrame(() => {
	document.body.classList.remove('u-no-transition')
})

