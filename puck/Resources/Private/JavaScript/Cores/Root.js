import { Core } from "~/_jcores"

export default class Root extends Core {
    static attributes = {}
    static injects = [
        'anchor-behavior',
        'scrollbar-width'
    ]
}