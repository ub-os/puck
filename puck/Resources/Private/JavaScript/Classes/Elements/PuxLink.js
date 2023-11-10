
import { noDragClick } from '../../General/Utility';
import PuxBehavior from "./PuxBehaviour.js";

export default class PuxLink extends PuxBehavior {
  static props = {
    to: '',
    target: ''
  }
  handleClick(e) {
    {
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
    }
  }
  openLink(newTab = false) {
    if (newTab) {
      window.open(this.to)
      window.focus()
    } else {
      window.location.href = this.to
    }
  }
  mount() {
    this.el.role = 'link'
    noDragClick(this.el, e => this.handleClick(e));
    return this
  }
}