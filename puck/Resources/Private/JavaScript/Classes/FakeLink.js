// CLASS FakeLink
//# html-structure: [data-link-to='{url}']  optional: [data-link-to-stop]
//# opens url on left click(not drag).
//# handler stops if event target is within a data-link-to-stop element.

import {noDragClick, getNode} from '../General/Functions';

export default class FakeLink {
  constructor(target) {
    this.node = getNode(target, 'FakeLink');
    this.node.role = 'link'
  }
  openLink(newTab = false) {
    if (newTab) {
      window.open(this.node.dataset.linkTo);
      window.focus();
    } else {
      window.location.href = this.node.dataset.linkTo;
    }
  }
  mount() {
    noDragClick(this.node, e => {
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