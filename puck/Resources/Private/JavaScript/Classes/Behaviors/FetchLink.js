
import { $, $$, id$, jsx } from "~/General/Aliases";
import { getElement, scrollTo } from '~/General/Utility'
import Listeners from "~/Classes/Listeners"
import { IntersectionManager } from "~/Classes/ObserverManager.js"
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior"

export default class FetchLink extends AbstractBehavior {
  static props = {
    url: '',
    mode: 'replace',
    contentId: '',
    trigger: 'click',
    scrollToContent: false,
    scrollOffset: 100,
    timing: {},
    disableAnchors: true,
    hideFrames: [
      { opacity: 1 },
      { opacity: 0 },
    ],
    showFrames: [
      { opacity: 0 },
      { opacity: 1 },
    ],
    replaceState: '',
    intersectionOptions: {}
  }
  replaceContent(html) {
    this.contentNode.innerHTML = html
  }

  appendContent(html, div) {
    const newRoot = div.firstChild
    this.contentNode.innerHTML = this.contentNode.innerHTML + html
    if (this.el.id) {
      const newFetchButton = newRoot.$(`#${this.el.id}`)
      const oldFetchButton = id$(this.el.id)
      if (oldFetchButton && newFetchButton) {
        oldFetchButton.parentNode.replaceChild(newFetchButton, oldFetchButton)
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
      const div = <div></div>
      div.innerHTML = html.trim()
      const newHtml = div.$(`#${this.contentId}`).innerHTML

      if (this.scrollToContent) {
        scrollTo(this.contentNode, this.scrollOffset)
      }

      const animation = this.contentNode.animate(
          this.hideFrames,
          this.timing)

      this.listeners.add(animation, 'finish', () => {
        if (this.mode === 'replace') {
          this.replaceContent(newHtml)
        }
        if (this.mode === 'append') {
          this.appendContent(newHtml, div)
        }
        this.contentNode.animate(
            this.showFrames,
            this.timing
        )
        if (this.replaceState) {
          window.history.replaceState({}, '', this.replaceState)
        }
        this.states.fetching = false
      })

    }).catch(err => {
      this.states.fetching = false
      console.warn('Link fetch went wrong.', err)
    })
  }

  mount() {
    this.contentNode = getElement(this.contentId, 'FetchLink contentNode')
    this.el.role = 'button'
    this.timing = {
      ...{
        duration: 500,
        easing: 'ease-in-out'
      },
      ...this.timing
    }
    this.intersectionOptions = {
      ...{
        root: null,
        rootMargin: '0px 0px 0px 0px',
        threshold: 0
      },
      ...this.intersectionOptions
    }
    this.states = {
      fetching: false,
    }
    this.listeners = new Listeners()
    if (!this.el || !this.url || !this.contentNode) {
      return {error: 'FetchLink: missing node, url or contentNode', fetchLink: this}
    }
    if (this.disableAnchors) {
      this.el.$$(`a`).forEach(anchor => {
        this.listeners.add(anchor, 'click', e => {
          e.preventDefault()
        })
      })
    }
    if (this.trigger === 'click') {
      this.listeners.add(this.el, 'click', e => {
        e.preventDefault()
        this.fetch()
      })
    }
    if (this.trigger === 'scrollIntoView') {
      IntersectionManager.addById(
          'fetch-link-' + this.el.id,
          this.el,
          (entry, observer) => {
            if (entry.isIntersecting) {
              this.fetch()
              IntersectionManager.remove('fetch-link-' + this.el.id)
            }
          },
          this.intersectionOptions
      )
    }
    return this
  }
  destroy() {
    this.listeners.destroy()
    IntersectionManager.remove('fetch-link-' + this.el.id)
  }
}