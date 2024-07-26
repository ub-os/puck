import Core from "~/_jcores/Core";

export default class Root extends Core {
    static attributes = {}
    static injects = [
        'anchor-behavior',
        'scrollbar-width'
    ]
    connect() {
    }
    disconnect() {
    }
}