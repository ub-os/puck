import Core from "~/_jcores/Core"

/**
 * @property {MyInject} myInjectCore
 * @property {Map} myUnitUnits
 * @property {HTMLElement} myUnitUnit
 */
// jsdoc is added for injects and units so the IDE does not complain
// register Core with Nexus.registerCore('my-core', MyCore)
// apply core to element with [data-core="my-core"]
export default class MyCore extends Core {
  // identifier constant is set from first argument of Nexus.registerCore()
  // => static identifier = 'my-core'

  static attributes = {
    // available as this.myAttr
    // serve as core options and state
    // syncs with [data-my-core:my-attr]
    // triggers myAttrChanged() callback when changed
    // because state should be stored in html, and this syncs with data-attributes, state properties should generally be defined here
    // attributes are converted to the type of the default value when read from data-attributes, so a json string will be converted to an object, for example
    myAttr: 'defaultValue'
  }

  static units = [
    // finds elements with [data-unit="my-core.my-unit"] within core element
    // finds elements with [data-unit="my-core.my-unit#{element.id}"] globally
    // elements available as this.myUnitUnits
    // triggers myUnitUnitConnected(el) and myUnitUnitDisconnected(el) callbacks when an element is added or removed
    'myUnit'
  ]

  static injects = [
    // injects another core on this.el
    // core available as this.myInjectCore
    // injected cores work like normal cores
    'my-inject'
  ]

  // alternatively, injects can be defined as object
  // the key is the name of the injected core
  // the value object will be passed to the core constructor, overriding the default attribute values
  static injects = {
    // core identifier
    'my-inject': {
      // constructor attributes
      injectAttr: 'overrideDefaultValue'
    }
  }

  // elements with [data-trigger="event->my-core::my-method"] trigger myMethod(event, { myParam }) when event is dispatched
  // also triggers on [data-trigger="event->my-core::my-method#{element.id}"] globally
  // elements have default trigger events, e.g. click, change, input, ..., so button[data-trigger="my-core::my-method"] is equivalent to button[data-trigger="click->my-core::my-method"]
  // static.triggerables is not required, methods will be triggered without it
  // when provided in static.triggerables, the value object will function as default parameters for the method
  // default parameters also allow the system to properly read the parameter data-attributes, otherwise always defaulting to string
  // trigger method parameters are set via [data-my-core::my-method:my-param="value"]
  static triggerables = {
    // method name
    myAction: {
      // default parameters
      myParam: 'defaultValue'
    }
  }

  // class constants, available as this.constructor.myConstant
  static myConstant = 'value'

  // is called when core is registered
  static registerCallback() { }

  // properties that are not synced with attributes
  myProp = 'defaultValue'

  // is called when core instance is first created
  initialize() { }

  // is called when element is added to DOM
  connect() {

    // this.el is the element this core is attached to
    console.log(this.el)

    // attributes
    console.log(this.myAttr)
    // will trigger myAttrChanged() callback, will set [data-my-core:my-attr="newValue"]
    this.myAttr = 'newValue'

    // non-synced properties
    console.log(this.myProp)

    // constants
    console.log(this.constructor.myConstant)

    // injected cores
    console.log(this.myInjectCore)

    // units as map, keys are element ids
    this.myUnitUnits.forEach(unitEl => {
        console.log(unitEl)
    })
    console.log(this.myUnitUnits.get('my-unit-1'))

    // get first unit
    console.log(this.myUnitUnit)

    // add event listeners, listeners.add() returns listener id
    this.myListenerId = this.listeners.add(this.el, 'click', () => {})
    // or set listener id manually
    this.listeners.addById('listener-id')(this.el, 'click', () => {})

    // remove event listeners by id
    this.listeners.remove(this.myListenerId)
    // or
    this.listeners.remove('listener-id')


    // add event listener to document.body
    // custom events can be dispatched on body with [data-trigger="event->my-event"] on any element
    // like with method triggers, the trigger event can be omitted, e.g. button[data-trigger="my-event"], which is equivalent to [data-trigger="click->my-event"]
    // by default, events are dispatched on the body, but can be overridden with [data-trigger="event->my-event#{element.id}"]
    this.myEventBusListener = this.listeners.add(document.body, 'my-event', event => {
      // event details can be set with [data-my-event_:my-detail="value"]
      // in contrast to method triggers, event triggers are not bound to a core, but serve as a global event bus
      // trigger events don't have to be registered to work, but can be configured with Nexus.registerEvent('my-event', { bubbles: true, detail: { myDetail: 'defaultValue' } }
      // like core attributes or triggerable method params, event details are converted to the type of the default value if default values are set via Nexus.registerEvent()
      console.log(event.detail.myDetail)
      // the original event triggering the custom event is available as event.detail.originalEvent
      console.log(event.detail.originalEvent)
    })

    return this
  }

  // is called when element is removed from DOM
  disconnect() {

    // remove all event listeners
    this.listeners.removeAll()

    return this
  }

  // is triggered when event (e.g. "click") is dispatched on elements with [data-trigger="event->my-core::my-method"]
  // event is passed as first argument
  // parameters are passed as second argument, default parameters can be set in static.triggerables
  // destructure parameters argument for more convenient syntax
  myMethod(event, { myParam }) {

    // method parameters are set on trigger element with [data-my-core::my-method:my-param="value"]
    console.log(myParam)

    // event target is available as event.target
    console.log(event.target)

    // event type is available as event.type
    console.log(event.type)

    // trigger element is available as event.currentTarget
    console.log(event.currentTarget)
  }

  // is triggered when this.myAttr / [data-my-core:my-attr] is changed
  myAttrChanged(oldValue, newValue) {
    console.log(`myAttr changed from ${oldValue} to ${newValue}`)
  }

  // is triggered when an attribute on the element is changed that does not correspond to a core attribute
  attributeChanged(name, oldValue, newValue) {
    console.log({ name, oldValue, newValue })
  }

  // is triggered when a unit is added
  myUnitUnitConnected(el) {
    // unit element is available as el
    el.setAttribute('aria-label', 'this my unit')
    // can be used to add event listeners, although the preferred method in most cases is to use triggers
    this.listeners.addById(`my-unit-click-${el.id}`)(el, 'click', () => {})
  }

  // is triggered when a unit is removed
  myUnitUnitDisconnected(el) {
    // unit element is available as el
    el.removeAttribute('aria-label')
    // remove event listeners
    this.listeners.remove(`my-unit-click-${el.id}`)
  }
}