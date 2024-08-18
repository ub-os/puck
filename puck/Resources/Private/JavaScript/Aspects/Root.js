import { ElementAspect } from "~/_jcores"

export default class Root extends ElementAspect {
    static attributes = {}
    static injectedAspects = [
        'anchor-scrolling',
        'scrollbar-width'
    ]
}