// CLASS ScrollSensitive
//# scrolling down adds scroll class to element
//# arguments: node selector, trigger distance from top, scroll class name

export default class ScrollSensitive {
  constructor(
      node,
      top = [{top: 5, media: 0}],
      parent = window,
      scrollClass = '-scroll'
  ) {
    this.el = node;
    this.scrollClass = scrollClass;
    this.parent = node.dataset.scrollParent ? document.getElementById(node.dataset.scrollParent) : parent;
    this.top = 0;
    top = node.dataset.scrollSensitive.length ?  JSON.parse(node.dataset.scrollSensitive).items : top;
    for (let item of top) {
      if (window.innerWidth > item.media) {
        this.top = item.top;
      }
    }
    if (this.el) {
      this.watch();
    }
  }
  toggleScrollClass() {
    if ((this.parent == window && document.documentElement.scrollTop < this.top) || (this.parent != window && this.parent.scrollTop < this.top)) {
      this.el.classList.remove(this.scrollClass);
    } else  {
      this.el.classList.add(this.scrollClass);
    }
  }
  watch() {
    const self = this;
    this.parent.addEventListener('scroll', event => {self.toggleScrollClass();});
  }
}