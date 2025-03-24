import { ElementTrait } from '~/stim2'

export default class Root extends ElementTrait {
	static props = {}
	static traits = {
		'anchor-scrolling': {
			scrollTopOnCurrentLink: false
		},
		'scrollbar-width': {
			sensorId: 'hanslanda'
		}
	}
}
