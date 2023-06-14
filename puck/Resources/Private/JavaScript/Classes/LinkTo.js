// CLASS FakeLink
//# html-structure: [data-link-to='{url}']  optional: [data-link-to-stop]
//# opens url on left click(not drag).
//# handler stops if event target is within a data-link-to-stop element.

import {noDragClick, getElement} from '../General/Functions';
import AbstractComponent from "./AbstractComponent.js";

export default class LinkTo extends AbstractComponent{
  constructor(target, { ...options } = {}) {
    super(target)
    Object.assign(this, { ...options })
    this.element.role = 'link'
  }
  openLink(newTab = false) {
    if (newTab) {
      window.open(this.element.dataset.linkTo);
      window.focus();
    } else {
      window.location.href = this.element.dataset.linkTo;
    }
  }
  mount() {
    noDragClick(this.element, e => {
      if (!e.target.closest('[data-link-to-stop]')) {
        if (e.button == 0) {
          this.openLink(e.metaKey);
        }
        if (e.button == 1) {
          this.openLink(true);
        }
      }
    });
    return this
  }
}