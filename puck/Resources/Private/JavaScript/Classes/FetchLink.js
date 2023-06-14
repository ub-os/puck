// CLASS FetchLink
//# changes (append or replace) the content of a element with the content of a fetched url

import {$, $$} from "../General/Aliases.js";
import {noDragClick, getElement, scrollTo} from '../General/Functions';
import {mountComponents} from "../Components/init";
import AbstractComponent from "./AbstractComponent";

export default class FetchLink extends AbstractComponent{
  constructor(target, {
    url,
    mode,
    contentId,
    trigger = 'click',
    scrollToContent = false,
    scrollOffset = 100,
    timing = {},
    hideAnimationFrames = [
      { opacity: 1 },
      { opacity: 0 },
    ],
    showAnimationFrames = [
      { opacity: 0 },
      { opacity: 1 },
    ],
    interSectionObserverOptions = {}
  }) {
    super(target)
    Object.assign(this, {
      url, mode, contentId, trigger, scrollToContent,
      scrollOffset, hideAnimationFrames, showAnimationFrames
    })
    this.contentElement = getElement(contentId, 'FetchLink contentElement')
    this.timing = {
      ...{
        duration: 500,
        easing: 'ease-in-out'
      },
      ...timing
    }
    this.interSectionObserverOptions = {
      ...{
        root: null,
        rootMargin: '0px 0px -40px 0px',
        threshold: 0
      },
      ...interSectionObserverOptions
    }
    this.states = {
      fetching: false,
    }
  }

  replaceContent(html) {
    this.contentElement.innerHTML = html
    mountComponents(this.contentElement)
  }

  appendContent(html, div) {
    const newRoot = div.firstChild
    this.contentElement.innerHTML = this.contentElement.innerHTML + html
    mountComponents(this.contentElement)
    if (this.element.id) {
      const newFetchButton = newRoot.$(`#${this.element.id}`)
      const oldFetchButton = $(`#${this.element.id}`)
      if (oldFetchButton && newFetchButton) {
        oldFetchButton.parentElement.replaceChild(newFetchButton, oldFetchButton)
        mountComponents(newFetchButton.parentElement)
      }
    }
    div.remove()
  }

  fetch() {
    if (this.states.fetching) {
      return
    }
    this.states.fetching = true
    fetch(this.url).then( response => {
      return response.text()
    }).then( html => {
      const div = document.createElement('div')
      div.innerHTML = html.trim()
      const newHtml = div.$(`#${this.contentId}`).innerHTML

      if (this.scrollToContent) {
        scrollTo(this.contentElement, this.scrollOffset)
      }

      const animation = this.contentElement.animate(
          this.hideAnimationFrames,
          this.timing)

      animation.addEventListener('finish', () => {
        if (this.mode === 'replace') {
          this.replaceContent(newHtml)
        }
        if (this.mode === 'append') {
          this.appendContent(newHtml, div)
        }
        this.contentElement.animate(
            this.showAnimationFrames,
            this.timing
        )
        if (this.element.href) {
          window.history.pushState({}, '', this.element.href)
        }
        this.states.fetching = false
      })

    }).catch(function (err) {
      this.states.fetching = false
      console.warn('Link fetch went wrong.', err)
    })
  }

  mount() {
    if (!this.element || !this.url || !this.contentElement) {
      return {error: 'FetchLink: missing element, url or contentElement', fetchLink: this}
    }
    if (this.trigger === 'click') {
      this.element.addEventListener('click', e => {
        e.preventDefault()
        this.fetch()
      });
    }
    if (this.trigger === 'scrollIntoView') {
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            observer.unobserve(entry.target)
            this.fetch()
          }
        })
      }, this.interSectionObserverOptions)
      observer.observe(this.element)
    }
    return this
  }
}