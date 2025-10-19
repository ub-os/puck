import { Controller } from '@oliveoilexpert/stim'
import { jsx } from '~/Utility/DomUtility'

export default class ScrollbarWidth extends Controller {
	static props = {
		sensorId: 'scrollbar-width-sensor',
		updateOnResize: true,
	}

	get scrollbarWidth() {
		return this.sensorEl.getBoundingClientRect().width - this.sensorEl.firstElementChild.getBoundingClientRect().width
	}
	updateScrollbarWidth = () => {
		document.documentElement.style.setProperty('--scrollbar-width', `${this.scrollbarWidth}px`)
	}
	connected() {
		this.sensorEl =
			document.getElementById(this.sensorId) ||
			this.element.appendChild(
				<div
					id={this.sensorId}
					data-hx-history-excluded="true"
					style="width:50px; visibility:hidden; overflow:scroll; height:0px; position: absolute; pointer-events: none;"
				>
					<div />
				</div>,
			)
		this.updateScrollbarWidth()
		if (this.updateOnResize) {
			this.resizeObserver = new ResizeObserver(this.updateScrollbarWidth)
			this.resizeObserver.observe(this.sensorEl.firstElementChild)
		}
	}
	disconnected() {
		this.sensorEl?.remove()
		this.resizeObserver?.disconnect()
	}
}
