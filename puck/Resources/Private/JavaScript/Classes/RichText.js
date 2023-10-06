import { $, $$, jsx } from '../General/Aliases';
import AbstractComponent from "./AbstractComponent.js";

export default class RichText extends AbstractComponent {
  constructor(target, { ...options }) {
    super(target, { ...options });
  }
  wrap(el, wrapper) {
    el.parentElement.insertBefore(wrapper, el)
    wrapper.appendChild(el)
  }
  mount() {
    this.element.querySelectorAll('a').forEach( a => {
      a.classList.add( location.hostname === a.hostname || !a.hostname.length ? '-local' : '-external' )
    })
    this.element.querySelectorAll('table').forEach(table => {
      const wrapper = document.createElement('div')
      wrapper.classList.add('table-wrapper')
      this.wrap(table, wrapper)
    })
    return this
  }
}