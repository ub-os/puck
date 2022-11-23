
// querySelector and eventListener shorthands
const $ = (selector) => document.querySelector(selector)
const $$ = (selector) => document.querySelectorAll(selector)

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

// jsx pragma method
const jsx = (tag, props, ...children) => {
  const element = document.createElement(tag)

  Object.entries(props || {}).forEach(([name, value]) => {
    if (name.startsWith('on') && name.toLowerCase() in window)
      element.addEventListener(name.toLowerCase().substr(2), value)
    else element.setAttribute(name, value.toString())
  })

  children.forEach((child) => {
    element.appendChild(
        child.nodeType === undefined ? document.createTextNode(child.toString()) : child
    )
  })
  return element
}


export {$, $$, jsx};