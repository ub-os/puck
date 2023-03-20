import { scrollTo } from '../General/Functions.js';

// CLASS SmoothHashLinks
//# smooth scroll to hash link targets instead of jumping
//# configurable vertical offset on scroll target for sticky header etc.

export default class SmoothHashLinks {
  constructor({ root = document.body, offset = 150 }) {
    this.hashLinks = root.querySelectorAll('a[href*="#"]');
    this.offset = offset;
  }
  mount() {
    const self = this;
    this.hashLinks.forEach(function(el, i) {
      el.onclick = function(event) {
        const href = el.href;
        if(el.pathname === window.location.pathname){
          event.preventDefault();
          const id = href.split('#')[1];
          if (id) scrollTo(id, self.offset);
        }
      };
    });
    window.onload = function () {
      const id = window.top.location.hash.substr(1);
      if (id) scrollTo(id, self.offset);
    };
    return this
  }
}
