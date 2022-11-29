// CLASS ScrollSensitive
//# scrolling down adds scroll class to element
//# arguments: node selector, trigger distance from top, scroll class name
import {getNode} from '../General/Functions';

export default class ScrollSensitive {
  constructor(
      target,
      {
        top = [{top: 5, media: 0}],
        parent = window,
        scrollClass = '--scroll'
      }
  ) {
    Object.assign(this, {top, parent, scrollClass});
    this.node = getNode(target, 'ScrollSensitive');
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
    this.parent.addEventListener('scroll', event => {this.toggleScrollClass();});
    return this
  }
}