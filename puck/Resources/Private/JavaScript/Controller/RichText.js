import { $, $$, $id, jsx } from '~/Utility/DomUtility'
import Controller from "~/Application/Controller.js";

// todo is this still needed?
export default class RichText extends Controller {
  connect() {
    this.el.$$('table').forEach(table => {
      const wrapper = <div class={'table-wrapper'}></div>
      table.parentElement.insertBefore(wrapper, table)
      wrapper.appendChild(table)
    })
    return this
  }
}