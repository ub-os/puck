import { ElementAspect } from "~/_jcores"

export default class Test extends ElementAspect {
    static attributes = {
        string: 'default',
        boolean: false,
        object: { key: 'value' },
    }
    static connectedElements = ['remote','descendant']
    static injectedAspects = []

    initialized() {
        console.log('Test initialized', this)
    }

    connected() {
        console.log('Test connected', this)
    }

    disconnected() {
        console.log('Test disconnected', this)
    }

    stringChanged(oldValue, newValue) {
        console.log('Test string changed', oldValue, newValue)
    }

    booleanChanged(oldValue, newValue) {
        console.log('Test boolean changed', oldValue, newValue)
    }

    objectChanged(oldValue, newValue) {
        console.log('Test object changed', oldValue, newValue)
    }

    remoteElementConnected(el) {
        console.log('Test remote element connected', el)
    }

    remoteElementDisconnected(el) {
        console.log('Test remote element disconnected', el)
    }

    descendantElementConnected(el) {
        console.log('Test descendant element connected', el)
    }

    descendantElementDisconnected(el) {
        console.log('Test descendant element disconnected', el)
    }
}