import { Aspect } from '@oliveoilexpert/stim'
import { $, $$, $id, jsx } from '~/Utility/DomUtility'

export default class ScrollbarWidth extends Aspect {
	static attributes = {
		sensorId: 'scrollbar-width-sensor',
		updateOnResize: true,
	}

	get scrollbarWidth() {
		return (
			this.sensorEl.getBoundingClientRect().width -
			this.sensorEl.firstElementChild.getBoundingClientRect().width
		)
	}
	updateScrollbarWidth = () => {
		document.documentElement.style.setProperty(
			'--scrollbar-width',
			`${this.scrollbarWidth}px`,
		)
	}
	connected() {
		this.sensorEl =
			$id(this.sensorId) ||
			this.el.appendChild(
				<div
					id={this.sensorId}
					data-render-excluded
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
		return this
	}
	disconnected() {
		this.sensorEl?.remove()
		this.resizeObserver?.disconnect()
	}
}
