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
    ]
  }) {
    Object.assign(this, {
      url, mode, contentId, scrollToContent, scrollOffset, hideAnimationFrames, showAnimationFrames})
    this.node = getNode(target, 'FetchLink')
    this.contentNode = getNode(contentId, 'FetchLink')
    this.timing = {
      ...{
        duration: 500,
        easing: 'ease-in-out'
      },
      ...timing
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

  mount() {
    if (!this.node || !this.url || !this.contentNode) {
      return {error: 'FetchLink: missing node, url or contentNode', fetchLink: this}
    }
    this.node.addEventListener('click', e => {
      e.preventDefault()
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
            window.history.replaceState({}, '', this.node.href)
          }

        })

      }).catch(function (err) {
        console.warn('Link fetch went wrong.', err)
      })
    });
    return this
  }
}