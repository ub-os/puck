function $(selector) {
  return document.querySelector(selector);
}
function $$(selector) {
  return document.querySelectorAll(selector);
}
Element.prototype.$ = function(selector) {
  return this.querySelector(selector);
};
Element.prototype.$$ = function(selector) {
  return this.querySelectorAll(selector);
};
Element.prototype.on = function(type, listener, options = {}) {
  return this.addEventListener(type, listener, options);
};
Element.prototype.off = function(type, listener, options = {}) {
  return this.removeEventListener(type, listener, options);
};
export {$, $$};