// CLASS LinkTo
//# html-structure: [data-link-to='{url}']  optional: [data-link-to-stop]
//# opens url on left click(not drag).
//# handler stops if event target is within a data-link-to-stop element.

import {noDragClick} from '../General/Functions';

export default class LinkTo {
  constructor() {
    this.links = document.querySelectorAll('[data-link-to]');
    this.watch();
  }
  openLink(link, newTab = false) {
    if (newTab) {
      window.open(link.dataset.linkTo);
      window.focus();
    } else {
      window.location.href = link.dataset.linkTo;
    }
  }
  watch() {
    const self = this;
    this.links.forEach(link => {
      noDragClick(link, e => {
        if (!e.target.closest('[data-link-to-stop]')) {
          if (e.button == 0) {
            self.openLink(link, e.metaKey);
          }
          if (e.button == 1) {
            self.openLink(link, true);
          }
        }
      });
    });
  }
}