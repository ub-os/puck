import { Controller } from '~/stim2'

export default class Root extends Controller {
	static props = {}
	static injects = {
		'anchor-scrolling': {
			scrollTopOnCurrentLink: false
		},
		'scrollbar-width': {
			sensorId: 'hanslanda'
		}
	}
}
