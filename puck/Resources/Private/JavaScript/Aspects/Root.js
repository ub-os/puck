import { Aspect } from "~/_nexus"

export default class Root extends Aspect {
    static attributes = {}
    static aspects = [
        'anchor-scrolling',
        'scrollbar-width'
    ]
}