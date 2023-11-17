import { $, $$, id$, jsx } from "~/General/Aliases";
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior"

export default class RichText extends AbstractBehavior {
  mount() {
    this.el.$$('a').forEach( a => {
      a.classList.add( location.hostname === a.hostname || !a.hostname.length ? '-local' : '-external' )
    })
    this.el.$$('table').forEach(table => {
      const wrapper = <div class={'table-wrapper'}></div>
      table.parentElement.insertBefore(wrapper, table)
      wrapper.appendChild(table)
    })
    return this
  }
}