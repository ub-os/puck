import { Aspect } from '@oliveoilexpert/stim'
import Splide from '@splidejs/splide'
import { Intersection } from '@splidejs/splide-extension-intersection'
import { EventListenerRegistry } from '~/Helper/EventListener.js'

/**
 * @property {Map} controlElements
 */
export default class Carousel extends Aspect {
	static attributes = {
		vertical: false,
		activeClass: '--active',
		baseSplideClass: 'splide',
		splideOptions: {},
	}
	static elements = ['control']
	//handlerSet = new EventHandlerSet()
	controlElementConnected(el) {
		el.ariaControls = this.el.id
		if (el.dataset['carousel.move.to'] == this.splide?.index) {
			el.classList.add(this.activeClass)
		}
	}
	move({ to } = {}) {
		this.splide.go(to)
	}
	connected() {
		this.biggestSlideHeight = 0
		const autoplay = this.splideOptions.autoplay ?? false
		this.splide = new Splide(this.el, {
			arrows: true,
			pagination: false,
			autoWidth: true,
			omitEnd: true,
			focus: 'left',
			resetProgress: false,
			intersection: {
				inView: {
					autoplay,
				},
				outView: {
					autoplay: false,
				},
			},
			classes: {
				arrows: `${this.baseSplideClass}__arrows`,
				arrow: `${this.baseSplideClass}__arrow`,
				prev: `${this.baseSplideClass}__arrow--prev`,
				next: `${this.baseSplideClass}__arrow--next`,
				pagination: `${this.baseSplideClass}__pagination`,
				page: `${this.baseSplideClass}__pagination__page`,
			},
			...this.splideOptions,
		})
		this.splide.on('move', (newIndex, oldIndex, destIndex) => {
			this.controlElements.forEach(control => {
				control.classList.remove(this.activeClass)
				if (control.dataset['carousel.move.to'] == this.splide?.index) {
					control.classList.add(this.activeClass)
				}
			})
		})
		this.splide.on('pagination:mounted', data => {
			data.list.setAttribute('data-history-excluded', '')
			if (autoplay) {
				data.items.forEach(page => {
					page.button.classList.add('-autoplay')
				})
			}
		})
		this.splide.on('autoplay:playing', rate => {
			this.el.style.setProperty('--splide-autoplay-progress', rate)
		})
		this.el.style.setProperty(
			'--splide-speed',
			`${this.splide.options.speed}ms`,
		)
		this.splide.mount({ Intersection })
		if (this.vertical) {
			// todo: replace with window.requestAnimationFrame ?
			window.addEventListener('load', () => {
				this.el.querySelectorAll('.splide__slide').forEach(slide => {
					const rect = slide.getBoundingClientRect()
					if (rect.height > this.biggestSlideHeight) {
						this.biggestSlideHeight = rect.height
					}
				})
				this.splide.options = {
					direction: 'ttb',
					height: this.biggestSlideHeight,
				}
			})
		}
		return this
	}
	disconnected() {
		this.splide.destroy()
	}
}
