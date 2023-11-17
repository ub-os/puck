import { noDragClick } from '~/General/Utility'
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior";

export default class Link extends AbstractBehavior {
  static props = {
    to: '',
    target: '',
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
    super.mount()
    this.el.role = 'link'
    noDragClick(this.el, e => this.handleClick(e));
    return this
  }
}