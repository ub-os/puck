
import { noDragClick } from '../../General/Utility';
import PuxElement from "./PuxElement.js";

export default class Link extends PuxElement {
  static props = {
    ...PuxElement.props,
    to: '',
    target: ''
  }

  constructor() {
    super()
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
    this.role = 'link'
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

//window.customElements.define('pux-link', Link);