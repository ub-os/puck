// CLASS SmoothHashLinks
//# smooth scroll to hash link targets instead of jumping
//# configurable vertical offset on scroll target for sticky header etc.

export default class SmoothHashLinks {
  constructor(
      offset = 200) {
    this.hashLinks = document.querySelectorAll('a[href*="#"]');
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
          if (id && document.getElementById(id) && getComputedStyle(document.getElementById(id)).position !== 'fixed') {
            const height = document.getElementById(id).getBoundingClientRect().top + document.documentElement.scrollTop - self.offset;
            window.scrollTo({
              top: height,
              left: 0,
              behavior: 'smooth'
            });
          }
        }
      };
    });
    window.onload = function () {
      const id = window.top.location.hash.substr(1);
      if(id && document.getElementById(id)) {
        const height = document.getElementById(id).getBoundingClientRect().top + document.documentElement.scrollTop - self.offset;
        if (id) {
          window.scrollTo({
            top: height,
            left: 0,
          });
        }
      }
    };
    return this
  }
}
