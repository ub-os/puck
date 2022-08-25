// CLASS BurgerMenu
//# default html-structure: [data-burger-el="parent"] contains [data-burger-el="menu"] and [data-burger-el="navigation"])
//# default selectors can be changed by passing them as constructor arguments, however "menu" and "navigation" must be within "parent"
//# click on "menu" toggles "navigation" visibility
//# click outside of "parent" removes "navigation" visibility if active
export default class BurgerMenu {
  constructor(
      burgerNode = document.querySelector('[data-burger-el="menu"]'),
      navigationNode = document.querySelector('[data-burger-el="navigation"]'),
      parentNode = document.querySelector('[data-burger-el="parent"]')) {
    this.burger = burgerNode;
    this.navigation = navigationNode;
    this.parent = parentNode;
    this.active = false;
    this.clickDelay = 500;
    this.clickDelayTimer = null;
    if (this.burger && this.navigation && this.parent) {
      this.watch();
    }
  }
  onToggle(fn) {
    const self = this;
    document.documentElement.addEventListener('click', function(event){
      if (!self.parent.contains(event.target) && self.active) {
        event.preventDefault();
        fn();
      }
    });
    this.burger.addEventListener('click', fn);
  }
  toggle() {
    const self = this;
    if (this.clickDelayTimer === null) {
      this.burger.classList.toggle('-active');
      this.navigation.classList.toggle('-open-mobile-nav');
      document.documentElement.classList.toggle('-burger-open');
      if (this.active) {
        this.burger.classList.add('-closing');
        this.clickDelayTimer = setTimeout(function () {
          self.burger.classList.remove('-closing');
          clearTimeout(this.clickDelayTimer);
          self.clickDelayTimer = null;
        }, self.clickDelay);
      }
      this.active = !this.active;
    }
  }
  watch() {
    const self = this;
    this.onToggle( function () {
      self.toggle();
    });
  }
}
