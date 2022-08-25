// CLASS Accordion / AccordionGroup
//# html-structure for Accordion element: [data-acc] > [data-acc-el="head"] + [data-acc-el="content"]
//# click on "head" toggles "content" visibility
//# AccordionGroup creates Accordion instances from all [data-acc]-children and puts them in a group
//# opening an Accordion within an AccordionGroup closes all other Accordions in that group
const toggleEvent = new Event('toggle');
class Accordion {
  constructor(node) {
    this.el = node;
    this.id = node.id;
    this.head = node.querySelector('[data-acc-el="head"]');
    this.content = node.querySelector('[data-acc-el="content"]');
    this.alwaysActive = node.hasAttribute('data-acc-always-active');
    this.startsOpen = node.hasAttribute('data-acc-starts-open');
    this.active = false;
    this.externalTrigger = [];
    this.valid = this.head != null && this.content != null;
    if (this.startsOpen) {
      this.open();
    }
    this.watch();
  }
  open() {
    this.active = true;
    this.el.classList.add('-open');
    this.content.style = `max-height: ${(this.content.scrollHeight + 100).toString()}px`;
    if (this.externalTrigger) {
      this.externalTrigger.forEach(function (trigger) {
        trigger.classList.add('-open');
      });
    }
  }
  close() {
    this.active = false;
    this.el.classList.remove('-open');
    this.content.style = `max-height: 0px`;
    if (this.externalTrigger) {
      this.externalTrigger.forEach(function (trigger) {
        trigger.classList.remove('-open');
      });
    }
  }
  toggle() {
    if (this.active && !this.alwaysActive) {
      this.close();
    } else {
      this.open();
    }
  }
  unbindToggle() {
    this.head.removeEventListener('click', this.dispatchToggle);
  }
  dispatchToggle() {
    this.dispatchEvent(toggleEvent);
  }
  watch() {
    const self = this;
    if (this.valid) {
      this.head.addEventListener('click', self.dispatchToggle);
      this.head.addEventListener('toggle', function (event) {
        event.preventDefault();
        self.toggle();
      });
    }
  }
}

class AccordionGroup {
  constructor(node) {
    this.element = node;
    this.openItem = null;
    this.items = this.initAccordions();
    this.externalTrigger = this.element.querySelectorAll('[data-trigger-acc]');
    this.startsOpen = node.hasAttribute('data-acc-starts-open');
    if (this.items) {
      this.watch();
    }
    if (this.startsOpen){
      let startOpenItem = this.items[node.dataset.accStartsOpen.valueOf()];
      if (startOpenItem) {
        startOpenItem.open();
        this.groupToggle(startOpenItem);
      }
    }
  }
  initAccordions() {
    return Array.prototype.slice.call(this.element.querySelectorAll('[data-acc="group"]')).map(x => new Accordion(x));
  }
  getAccordionById(id) {
    return this.items.filter(x => x.id === id)[0];
  }
  groupToggle(item) {
    if (item.active) {
      if (this.openItem && this.openItem !== item) {
        this.openItem.close();
      }
      this.openItem = item;
      this.element.classList.add('-open-acc-child');
    } else if (!item.alwaysActive){
      this.element.classList.remove('-open-acc-child');
    }
  }
  watch() {
    const self = this;
    this.items.forEach(function (item) {
      if (item.valid) {
        item.head.addEventListener('toggle', function () {
          self.groupToggle(item);
        });
      }
    });
    this.externalTrigger.forEach(function (trigger) {
      const acc = self.getAccordionById(trigger.dataset.triggerAcc);
      acc.externalTrigger.push(trigger);
      if (acc && acc.valid) {
        trigger.addEventListener('click', function () {
          acc.head.dispatchEvent(toggleEvent);
        });
      }
    });
  }
}
export {Accordion, AccordionGroup, toggleEvent};
