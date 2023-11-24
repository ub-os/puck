// document shorthands
const $ = (selector) => document.querySelector(selector)
const $$ = (selector) => document.querySelectorAll(selector)
const id$ = (id) => document.getElementById(id)

Element.prototype.$ = function(selector) {
  return this.querySelector(selector);
};
Element.prototype.$$ = function(selector) {
  return this.querySelectorAll(selector);
};
// jsx pragma method
const jsx = (tag, props, ...children) => {
  const element = document.createElement(tag)

  Object.entries(props || {}).forEach(([name, value]) => {
    if (name.startsWith('on') && name.toLowerCase() in window)
      element.addEventListener(name.toLowerCase().substring(2), value)
    else element.setAttribute(name, value.toString())
  })

  children.forEach((child) => {
    element.appendChild(
        child.nodeName === undefined ? document.createTextNode(child.toString()) : child
    )
  })
  return element
}

export { $, $$, id$, jsx };