// CLASS Modal
//# html-structure for Accordion element: [data-modal] > [data-modal-window], [data-modal-close] && data-modal-trigger={modal.id}
//# click on "trigger" toggles "content" visibility
export default class Modal {
  constructor(node) {
    document.querySelector('[data-page-modals-container]').appendChild(node);
    this.el = node;
    this.id = node.id;
    this.closer = node.querySelector('[data-modal-close]');
    this.window = node.querySelector(`[data-modal-window]`);
    this.trigger = document.querySelectorAll(`[data-modal-trigger="${this.id}"]`);
    this.active = false;
    this.valid = this.trigger != null;
    this.watch();
  }
  open() {
    this.active = true;
    this.el.classList.add('-open');
    document.documentElement.classList.add('-modal-open');
    this.trigger.forEach(function (trigger) {
      trigger.classList.add('-open');
    });
  }
  close() {
    this.active = false;
    this.el.classList.remove('-open');
    document.documentElement.classList.remove('-modal-open');
    this.trigger.forEach(function (trigger) {
      trigger.classList.remove('-open');
    });
  }
  toggle() {
    if (this.active) {
      this.close();
    } else {
      this.open();
    }
  }
  watch() {
    const self = this;
    if (this.valid) {
      self.closer.addEventListener('click', function (event) {
        self.close();
      });
      self.el.addEventListener('click', function (event) {
        if (!self.window.contains(event.target)) {
          event.preventDefault();
          self.close();
        }
      });
      self.trigger.forEach(function(trigger){
        trigger.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          self.open();
        });
      });
    }
  }
}
