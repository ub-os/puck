import Controller from "~/Application/Controller.js";

export default class MyController extends Controller {
  static props = {
    yourArgumentProperty: 'defaultValue'
  }
  static yourConstantProperty = 'defaultValue'
  yourNonArgumentProperty = 'defaultValue'
  connect() {
    return this
  }
  disconnect() {
  }
}