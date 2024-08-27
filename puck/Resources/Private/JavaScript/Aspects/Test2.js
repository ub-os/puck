import Test from './Test'

export default class Test2 extends Test {
    static attributes = {
        string: 'default',
        boolean: false,
        object: { key: 'value' },
    }
    static connectedElements = ['remote','descendant']
    static injectedAspects = []

    initialized() {
        console.log('Test2 initialized', this)
    }

    connected() {
        console.log('Test2 connected', this)
    }

    disconnected() {
        console.log('Test2 disconnected', this)
    }

    stringChanged(oldValue, newValue) {
        console.log('Test2 string changed', oldValue, newValue)
    }

    booleanChanged(oldValue, newValue) {
        console.log('Test2 boolean changed', oldValue, newValue)
    }

    objectChanged(oldValue, newValue) {
        console.log('Test2 object changed', oldValue, newValue)
    }

    remoteElementConnected(el) {
        console.log('Test2 remote element connected', el)
    }

    remoteElementDisconnected(el) {
        console.log('Test2 remote element disconnected', el)
    }

    descendantElementConnected(el) {
        console.log('Test2 descendant element connected', el)
    }

    descendantElementDisconnected(el) {
        console.log('Test2 descendant element disconnected', el)
    }
}