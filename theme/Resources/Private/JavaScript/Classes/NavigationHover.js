// CLASS NavigationHover
//# html-structure: [data-navigation-hover] > [data-navigation-hover-item]
//# adds hover class to navigation on mouse over item
export default class NavigationHover {
  constructor(
      node,
      hoverClass = '-child-hover'
  ) {
    this.node = node;
    this.items = node.querySelectorAll('[data-navigation-hover-item]');
    this.hoveredItem = undefined;
    this.hoverClass = hoverClass;
    this.previousHovered = false;
    this.previousOffset = 0;
    this.bullet = node.querySelector('.m-page-header-navigation__hover-bullet');
    this.watch();
  }
  watch() {
    const self = this;
    this.items.forEach(item => {
      item.onmouseover = event => {
        self.node.classList.add(self.hoverClass);
/*        if (item !== this.hoveredItem) {
          let prevItems = [],
              node = item.parentNode.parentNode.firstChild,
              offset = item.parentNode.offsetWidth/2;
          while ( node && node !== item.parentNode) {
            if (node.offsetWidth) {
              prevItems.push( node );
              offset += node.offsetWidth;
            }
            node = node.nextElementSibling || node.nextSibling;
          }
          self.node.style.setProperty('--hover-bullet-translateX', offset+'px');
          if(!self.previousHovered) {
            self.bullet.style.setProperty('transition', 'opacity .15s');
            self.bullet.animate([
              { transform: `translateX(${offset}px) scale(.2)`,},
              { transform: `translateX(${offset}px) scale(1)` },
            ], {
              duration: 150,
              iterations: 1
            });
          } else {
            self.bullet.animate([
              { transform: `translateX(${self.previousOffset}px) scale(1)`,},
              { transform: `translateX(${(self.previousOffset + offset) / 2}px) scale(.5)`,},
              { transform: `translateX(${offset}px) scale(1)`,},
            ], {
              duration: 200,
              iterations: 1,
              easing: 'ease-in-out'
            });
          }
          self.hoveredItem = item;
          self.previousHovered = true;
          self.previousOffset = offset;
          setTimeout(()=>{self.previousHovered = true;}, 300);
          self.bullet.style.setProperty('transition', 'transform .15s ease-in-out, opacity .15s');
        }*/
      };
      item.onmouseout = event => {
        self.node.classList.remove(self.hoverClass);
        //setTimeout(()=>{self.previousHovered = false;}, 300);
      };
    });
  }
}