// CLASS FetchLink
//# changes (append or replace) the content of a node with the content of a fetched url

import {$, $$} from "../General/Aliases.js";
import {noDragClick, getNode, scrollTo} from '../General/Functions';
import {mountComponents} from "../Components/init.js";

export default class FetchLink {
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
    Object.assign(this, {
      url, mode, contentId, trigger, scrollToContent, scrollOffset, hideAnimationFrames, showAnimationFrames})
    this.node = getNode(target, 'FetchLink node')
    this.contentNode = getNode(contentId, 'FetchLink contentNode')
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
    this.contentNode.innerHTML = html
    mountComponents(this.contentNode)
  }

  appendContent(html, div) {
    const newRoot = div.firstChild
    this.contentNode.innerHTML = this.contentNode.innerHTML + html
    mountComponents(this.contentNode)
    if (this.node.id) {
      const newFetchButton = newRoot.$(`#${this.node.id}`)
      const oldFetchButton = $(`#${this.node.id}`)
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
    fetch(this.url).then( response => {
      return response.text()
    }).then( html => {
      const div = document.createElement('div')
      div.innerHTML = html.trim()
      const newHtml = div.$(`#${this.contentId}`).innerHTML

      if (this.scrollToContent) {
        scrollTo(this.contentNode, this.scrollOffset)
      }

      const animation = this.contentNode.animate(
          this.hideAnimationFrames,
          this.timing)

      animation.addEventListener('finish', () => {
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
        if (this.node.href) {
          window.history.pushState({}, '', this.node.href)
        }
        this.states.fetching = false
      })

    }).catch(function (err) {
      this.states.fetching = false
      console.warn('Link fetch went wrong.', err)
    })
  }

  mount() {
    if (!this.node || !this.url || !this.contentNode) {
      return {error: 'FetchLink: missing node, url or contentNode', fetchLink: this}
    }
    if (this.trigger === 'click') {
      this.node.addEventListener('click', e => {
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
      observer.observe(this.node)
    }
    return this
  }
}