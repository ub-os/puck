import { scrollTo } from '../General/Functions.js';

// CLASS SmoothHashLinks
//# smooth scroll to hash link targets instead of jumping
//# configurable vertical offset on scroll target for sticky header etc.

export default class SmoothHashLinks {
  constructor({ root = document.body, offset = 50, activeClass = '--active' }) {
    this.hashLinks = root.querySelectorAll('a[href*="#"]');
    this.offset = offset;
    this.activeClass = activeClass;
  }
  mount() {
    const self = this;
    this.hashLinks.forEach(function(el, i) {
      if(el.pathname === window.location.pathname) {

        // htmx integration
        if (el['htmx-internal-data']) { el['htmx-internal-data'].boosted = false; }
        else { el.setAttribute('hx-boost', 'false')}

        el.onclick = function(event) {
          const href = el.href;
          event.preventDefault();
          const id = href.split('#')[1];
          if (!id) return;
          const anchor = document.getElementById(id);
          if (!anchor) return;
          if (anchor.hasAttribute('data-menu-anchor')) {
            scrollTo(document.getElementById(id).nextElementSibling, self.offset);
          } else {
            scrollTo(id, self.offset);
          }
          history.replaceState({}, '', href);
        };
      }
    });
    window.requestAnimationFrame(() => {
      const id = window.location.hash.substring(1).split('?')[0];
      if (id) scrollTo(id, self.offset);
    });
    return this
  }
}
