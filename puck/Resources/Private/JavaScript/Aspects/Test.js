import { Aspect } from "~/_nexus"

export default class Test extends Aspect {
    static attributes = {
        string: 'default',
        boolean: false,
        object: { key: 'value' },
    }
    static elements = ['remote','descendant']
    static aspects = []

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