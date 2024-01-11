import Controller from "~/_Stim/Controller"

/**
 * @property {MyInject} myInjectController
 */
// jsdoc is added for injects so the IDE does not complain
// register Controller with App.register('my-controller', MyController)
// apply controller to element with [data-controller="my-controller"]
export default class MyController extends Controller {
  // identifier constant is set from first argument of App.register()
  // => static identifier = 'my-controller'

  static attributes = {
    // available as this.myAttr
    // serve as controller arguments
    // syncs with [data-my-controller:my-attr]
    // triggers myAttrChanged() callback when changed
    // because state should be stored in html, and this syncs with data-attributes, state properties should generally be defined here
    // attributes are converted to the type of the default value when read from data-attributes, so a json string will be converted to an object, for example
    myAttr: 'defaultValue'
  }

  static targets = [
    // finds elements with [data-target="my-controller.my-target"] within controller element
    // finds elements with [data-target="my-controller.my-target#{element.id}"] globally
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
  // the value object will be passed to the controller constructor, overriding the default attribute values
  static injects = {
    // controller identifier
    'my-inject': {
      // constructor attributes
      injectAttr: 'overrideDefaultValue'
    }
  }

  // actions trigger on elements with [data-action="event->my-controller::my-method"] within controller element
  // also triggers on [data-action="event->my-controller::my-action#{element.id}"] globally
  // elements have default trigger events, e.g. click, change, input, ..., so button[data-action="my-controller::my-method"] is equivalent to button[data-action="click->my-controller::my-method"]
  // static.actions is not required, actions will work without it
  // when provided, the value object will function as default parameters for the action
  // default parameters also allow the system to properly read the parameter data-attributes, otherwise always defaulting to string
  // action parameters are set via [data-my-controller::my-method:my-param="value"]
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
  myProp = 'defaultValue'

  // is called when controller instance is first created
  initialize() { }

  // is called when element is added to DOM
  connect() {

    // this.el is the element this controller is attached to
    console.log(this.el)

    // attributes
    console.log(this.myAttr)
    // will trigger myAttrChanged() callback, will set [data-my-controller:my-attr="newValue"]
    this.myAttr = 'newValue'

    // non-synced properties
    console.log(this.myProp)

    // constants
    console.log(this.constructor.myConstant)

    // injected controllers
    console.log(this.myInjectController)

    // targets as map, keys are element ids
    this.myTargetTargets.forEach(targetEl => {
        console.log(targetEl)
    })
    console.log(this.myTargetTargets.get('my-target-1'))

    // get first target
    console.log(this.myTargetTarget)

    // add event listeners, listeners.add() returns listener id
    this.myListenerId = this.listeners.add(this.el, 'click', () => {})
    // or set listener id manually
    this.listeners.addById('listener-id')(this.el, 'click', () => {})

    // remove event listeners by id
    this.listeners.remove(this.myListenerId)
    // or
    this.listeners.remove('listener-id')


    // add event listener to document.body
    // custom events can be dispatched on body with [data-event="event->my-event"] on any element
    // similar to actions, the trigger event can be omitted, e.g. button[data-event="my-event"], which is equivalent to [data-event="click->my-event"]
    // by default, events are dispatched on the body, but can be overridden with [data-event="event->my-event#{element.id}"]
    this.myEventBusListener = this.listeners.add(document.body, 'my-event', event => {
      // event details can be set with [data-my-event_:my-detail="value"]
      // in contrast to actions, events are not bound to a controller, but serve as a global event bus
      // events don't have to be registered to work, but can be configured with App.registerEvent('my-event', { bubbles: true, detail: { myDetail: 'defaultValue' } }
      // like controller attributes or action params, event details are converted to the type of the default value if default values are set via App.registerEvent()
      console.log(event.detail.myDetail)
      // the original event triggering the custom event is available as event.detail.triggerEvent
      console.log(event.detail.triggerEvent)
    })

    return this
  }

  // is called when element is removed from DOM
  disconnect() {

    // remove all event listeners
    this.listeners.removeAll()

    return this
  }

  // is triggered when event (e.g. "click") is dispatched on elements with [data-action="event->my-controller::my-action"]
  // event is passed as first argument
  // parameters are passed as second argument, default parameters can be set in static.actions
  // destructure parameters argument for more convenient syntax
  myAction(event, { myParam }) {

    // method parameters are set on action element with [data-my-controller::my-action:my-param="value"]
    console.log(myParam)

    // event target is available as event.target
    console.log(event.target)

    // event type is available as event.type
    console.log(event.type)

    // action element is available as event.currentTarget
    console.log(event.currentTarget)
  }

  // is triggered when this.myAttr is changed / when [data-my-controller:my-attr] is changed
  myAttrChanged(oldValue, newValue) {
    console.log(`myAttr changed from ${oldValue} to ${newValue}`)
  }

  // is triggered when an attribute on the element is changed that does not correspond to a controller attribute
  attributeChanged(name, oldValue, newValue) {
    console.log({ name, oldValue, newValue })
  }

  // is triggered when a target is added
  myTargetConnected(el) {
    // target element is available as el
    el.setAttribute('aria-label', 'this my target')
    // can be used to add event listeners, although the preferred method in most cases is to use actions
    this.listeners.addById(`my-target-click-${el.id}`)(el, 'click', () => {})
  }

  // is triggered when a target is removed
  myTargetDisconnected(el) {
    // target element is available as el
    el.removeAttribute('aria-label')
    // remove event listeners
    this.listeners.remove(`my-target-click-${el.id}`)
  }


}