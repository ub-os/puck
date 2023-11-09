
import { noDragClick } from '../../General/Functions';
import PropElement from "./PropElement.js";

export default class Link extends PropElement {
  static props = {
    to: '',
    target: ''
  }

  constructor() {
    super()
    this.role = 'link'
  }

  openLink(newTab = false) {
    if (newTab) {
      window.open(this.to);
      window.focus();
    } else {
      window.location.href = this.to;
    }
  }
  mount() {
    noDragClick(this, e => {
      if (!e.target.closest('[data-link-stop]')) {
        if (this.target == '_blank') {
          this.openLink(true);
          return
        }
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

window.customElements.define('pux-link', Link);