import Controller from "~/Application/Controller.js";

/**
 * @property {MyInject} myInjectController
 */
// jsdoc is added for injects so the IDE does not complain
// register Controller with App.register('my-controller', MyController)
// apply controller to element with [data-controller="my-controller"]
export default class MyController extends Controller {
  // identifier constant is first argument of App.register()
  // => static identifier = 'my-controller'

  static props = {
    // available as this.myProp
    // serves as constructor argument
    // syncs with [data-my-controller:my-prop]
    // triggers myPropChanged() callback when changed
    // because state should be stored in html, and this syncs with data-attributes, state properties should generally be defined here
    myProp: 'defaultValue'
  }

  static targets = [
    // finds elements with [data-target="myTarget@my-controller"] within controller element
    // finds elements with [data-target="myTarget@my-controller#{element.id}"] globally
    // elements available as this.myTargetTargets
    // triggers myTargetConnected(el) and myTargetDisconnected(el) callbacks when an element is added or removed
    'myTarget'
  ]

  static injects = [
      // injects another controller on this.el
      // controller available as this.myInjectController
      // injected controllers work like normal controllers
      'my-inject'
  ]

  // alternatively, injects can be defined as object
  // the key is the name of the injected controller
  // the value object will be passed to the controller constructor, overriding the default prop values
  static injects = {
    // controller identifier
    'my-inject': {
      // constructor props
      injectProp: 'overrideDefaultValue'
    }
  }

  // actions trigger on elements with [data-action="event->myAction@my-controller"]
  // static.actions is not required, actions will work without it
  // when provided, the value object will function as default parameters for the action
  // default parameters also allow the system to properly read the parameter data-attributes, otherwise always defaulting to string
  // action parameters are set via [data-my-controller:param:my-param="value"]
  static actions = {
    // method name
    myAction: {
      // default parameters
      myParam: 'defaultValue'
    }
  }

  // class constants, available as this.constructor.myConstant
  static myConstant = 'value'

  // is called when controller is registered
  static registerCallback() { }

  // properties that are not synced with attributes
  myInternalProp = 'defaultValue'

  // is called when controller instance is first created
  initialize() { }

  // is called when element is added to DOM
  connect() {

    // this.el is the element this controller is attached to
    console.log(this.el)

    // props
    console.log(this.myProp)
    // will trigger myPropChanged() callback, will set [data-my-controller:my-prop="newValue"]
    this.myProp = 'newValue'

    // non-synced properties
    console.log(this.myInternalProp)

    // constants
    console.log(this.constructor.myConstant)

    // injected controllers
    console.log(this.myInjectController)

    // targets as map
    console.log(this.myTargetTargets)

    // add event listeners
    this.myListenerId = this.listeners.add(this.el, 'click', () => {})
    // or
    this.listeners.addById('listener-id',  this.el, 'click', () => {})

    // remove event listeners
    this.listeners.remove(this.myListenerId)
    // or
    this.listeners.remove('listener-id')

    return this
  }

  // is called when element is removed from DOM
  disconnect() {

    // remove all event listeners
    this.listeners.removeAll()

    return this
  }

  // is triggered when event (e.g. "click") is dispatched on elements with [data-action="event->myAction@my-controller"]
  // also triggers on [data-action="event->myAction@my-controller#{element.id}"] globally
  // event is passed as first argument
  // parameters are passed as second argument, default parameters can be set in static.actions
  // destructure parameters argument for more convenient syntax
  myAction(event, { myParam }) {

    // method parameters are set on action element with [data-my-controller:param:my-param="value"]
    console.log(myParam)

    // event target is available as event.target
    console.log(event.target)

    // event type is available as event.type
    console.log(event.type)

    // action element is available as event.actionElement
    console.log(event.actionElement)
  }

  // is triggered when this.myProp is changed
  myPropChanged(oldValue, newValue) {
    console.log(`myProp changed from ${oldValue} to ${newValue}`)
  }

  // is triggered when a target is added
  myTargetConnected(el) {
    // target element is available as el
    el.setAttribute('aria-label', 'this my target')
    // can be used to add event listeners, although the preferred method in most cases is to use actions
    this.listeners.addById(`my-target-click-${el.id}`, el, 'click', () => {})
  }

  // is triggered when a target is removed
  myTargetDisconnected(el) {
    // target element is available as el
    el.removeAttribute('aria-label')
    // remove event listeners
    this.listeners.remove(`my-target-click-${el.id}`)
  }
}