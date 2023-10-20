// CLASS FetchLink
//# changes (append or replace) the content of a node with the content of a fetched url

import {$, $$} from "../General/Aliases.js";
import {getElement, scrollTo} from '../General/Functions';
import {mountComponents} from "../Components/init.js";
import AbstractComponent from "./AbstractComponent.js";
import Listeners from "./Listeners.js";
import { IntersectionManager } from "./ObserverManager.js";

export default class FetchLink extends AbstractComponent {
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
    this.contentNode = getElement(contentId, 'FetchLink contentNode')
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
        rootMargin: '0px 0px 0px 0px',
        threshold: 0
      },
      ...interSectionObserverOptions
    }
    this.states = {
      fetching: false,
    }
  }

  replaceContent(html) {
    this.contentNode.innerHTML = html
    mountComponents(this.contentNode)
  }

  appendContent(html, div) {
    const newRoot = div.firstChild
    this.contentNode.innerHTML = this.contentNode.innerHTML + html
    mountComponents(this.contentNode)
    if (this.element.id) {
      const newFetchButton = newRoot.$(`#${this.element.id}`)
      const oldFetchButton = $(`#${this.element.id}`)
      if (oldFetchButton && newFetchButton) {
        oldFetchButton.parentNode.replaceChild(newFetchButton, oldFetchButton)
        mountComponents(newFetchButton.parentNode)
      }
    }
    div.remove()
  }

  fetch() {
    if (this.states.fetching) {
      return
    }
    this.states.fetching = true
    fetch(this.url).then(response => {
      return response.text()
    }).then(html => {
      const div = document.createElement('div')
      div.innerHTML = html.trim()
      const newHtml = div.$(`#${this.contentId}`).innerHTML

      if (this.scrollToContent) {
        scrollTo(this.contentNode, this.scrollOffset)
      }

      const animation = this.contentNode.animate(
          this.hideAnimationFrames,
          this.timing)

      this.listeners.add(animation, 'finish', () => {
        if (this.mode === 'replace') {
          this.replaceContent(newHtml)
        }
        if (this.mode === 'append') {
          this.appendContent(newHtml, div)
        }
        this.contentNode.animate(
            this.showAnimationFrames,
            this.timing
        )
        if (this.element.href) {
          window.history.replaceState({}, '', this.element.href)
        }
        this.states.fetching = false
      })

    }).catch(err => {
      this.states.fetching = false
      console.warn('Link fetch went wrong.', err)
    })
  }

  mount() {
    this.listeners = new Listeners()
    if (!this.element || !this.url || !this.contentNode) {
      return {error: 'FetchLink: missing node, url or contentNode', fetchLink: this}
    }
    if (this.trigger === 'click') {
      this.listeners.add(this.element, 'click', e => {
        e.preventDefault()
        this.fetch()
      })
    }
    if (this.trigger === 'scrollIntoView') {
      IntersectionManager.addById('fch-lk-' + this.id, this.element, (entry, observer) => {
        if (entry.isIntersecting) {
          this.fetch()
          IntersectionManager.remove('fch-lk-' + this.id)
        }
      }, this.interSectionObserverOptions)
    }
    return this
  }
  destroy() {
    this.listeners.destroy()
    IntersectionManager.remove('fch-lk-' + this.id)
  }
}