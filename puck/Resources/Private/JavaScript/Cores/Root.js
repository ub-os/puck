import { ElementAspect } from "~/_jcores"

export default class Root extends ElementAspect {
    static attributes = {}
    static injectedAspects = [
        'anchor-behavior',
        'scrollbar-width'
    ]
}