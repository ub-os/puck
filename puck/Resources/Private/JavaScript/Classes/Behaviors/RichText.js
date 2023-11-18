import { $, $$, id$, jsx } from "~/General/Aliases";
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior"

// todo is this still needed?
export default class RichText extends AbstractBehavior {
  mount() {
    this.el.$$('table').forEach(table => {
      const wrapper = <div class={'table-wrapper'}></div>
      table.parentElement.insertBefore(wrapper, table)
      wrapper.appendChild(table)
    })
    return this
  }
}