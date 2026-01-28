import { Controller } from '@amdeu/stim'
import Splide from '@splidejs/splide'
import { Intersection } from '@splidejs/splide-extension-intersection'
import {ListenerRegistry} from "~/Helper/ListenerRegistry.js"
import { jsx } from '~/Utility/DomUtility'

export default class Carousel extends Controller {
	static props = {
		vertical: false,
		activeClass: '--active',
		baseSplideClass: 'splide',
		autoplayToggle: false,
		autoplayPaused: false,
		splideOptions: {},
	}
	static targets = ['control']
	listeners = new ListenerRegistry()

	controlTargetConnected(el) {
		el.ariaControls = this.element.id
		if (el.dataset['carousel.move.to'] == this.splide?.index) {
			el.classList.add(this.activeClass)
		}
	}

	createAutoplayToggleElement() {
		return (
			<button class={`${this.baseSplideClass}__autoplay-toggle`}
					  type="button"
					  data-hx-history-excluded="true"
					  aria-pressed="false"
					  title="Play/Pause Autoplay">
				<i class={`icon--pause-solid`}/>
			</button>
		)
	}

	toggleAutoplay() {
		const player = this.splide.Components.Autoplay
		this.autoplayPaused = !this.autoplayPaused
		if (this.autoplayPaused) {
			player.pause()
		} else {
			player.play()
		}
	}

	autoplayPausedPropChanged(oldValue, newValue) {
		if (!this.autoplayToggleElement) return
		this.autoplayToggleElement.setAttribute('aria-pressed', !newValue)
		this.autoplayToggleElement.classList.toggle('--paused', newValue)
		this.autoplayToggleElement.classList.toggle('--playing', !newValue)
	}

	move({ to = '>' }) {
		this.splide.go(to)
	}

	connected() {
		this.biggestSlideHeight = 0

		// splideOptions may be passed from a php array where a "0" is used as a falsy value
		for (const key in this.splideOptions) {
			if (this.splideOptions[key] === '0') this.splideOptions[key] = 0
		}
		const autoplay = this.splideOptions.autoplay ?? false
		this.splide = new Splide(this.element, {
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
			this.controlTargets.forEach(control => {
				control.classList.remove(this.activeClass)
				if (control.dataset['carousel.move.to'] == this.splide?.index) {
					control.classList.add(this.activeClass)
				}
			})
			// force eager loading of the current slide and the previous and next slides
			// default lazy loading will often only start after the transition is done
			const slides = [newIndex - 1, newIndex, newIndex + 1]
			slides.forEach(index => {
				if (index >= 0 && index < this.splide.length) {
					const slide = this.splide.Components.Slides.getAt(index)
					if (slide) {
						const img = slide.slide.querySelector('img[loading="lazy"]')
						if (img && !img.complete) {
							img.loading = 'eager'
						}
					}
				}
			})
		})

		this.controlContainer = this.element.querySelector(`.${this.baseSplideClass}__controls`) ?? this.element

		this.splide.on('pagination:mounted', data => {
			if (autoplay) {
				data.items.forEach(page => {
					page.button.classList.add('-autoplay')
				})
			}
			data.list.setAttribute('data-hx-history-excluded', 'true')
			this.controlContainer.appendChild(data.list)
		})
		this.splide.on('autoplay:playing', rate => {
			this.element.style.setProperty('--splide-autoplay-progress', rate.toString())
		})
		this.element.style.setProperty('--splide-speed', `${this.splide.options.speed}ms`)

		if (this.autoplayToggle && autoplay) {
			this.autoplayToggleElement = this.createAutoplayToggleElement()
			this.listeners.add(this.autoplayToggleElement, 'click', () => { this.toggleAutoplay() })
			this.controlContainer.appendChild(this.autoplayToggleElement)
		}

		this.splide.mount({ Intersection })

		if (this.vertical) {
			// todo: replace with window.requestAnimationFrame ?
			window.addEventListener('load', () => {
				this.element.querySelectorAll('.splide__slide').forEach(slide => {
					const rect = slide.getBoundingClientRect()
					if (rect.height > this.biggestSlideHeight) {
						this.biggestSlideHeight = rect.height
					}
				})
				this.splide.options = {
					direction: 'ttb',
					height: this.biggestSlideHeight,
				}
			}, {once: true})
		}
	}

	disconnected() {
		this.splide.destroy()
	}
}