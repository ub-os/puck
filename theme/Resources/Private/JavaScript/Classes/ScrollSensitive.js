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
    this.node = node;
    this.scrollClass = scrollClass;
    this.parent = node.dataset.scrollParent ? document.getElementById(node.dataset.scrollParent) : parent;
    this.top = 0;
    top = node.dataset.scrollSensitive ?  JSON.parse(node.dataset.scrollSensitive).items : top;
    for (let item of top) {
      if (window.innerWidth > item.media) {
        this.top = item.top;
      }
    }
  }
  toggleScrollClass() {
    if ((this.parent == window && document.documentElement.scrollTop < this.top) || (this.parent != window && this.parent.scrollTop < this.top)) {
      this.node.classList.remove(this.scrollClass);
    } else  {
      this.node.classList.add(this.scrollClass);
    }
  }
  mount() {
    const self = this;
    this.parent.addEventListener('scroll', event => {self.toggleScrollClass();});
    return this
  }
}