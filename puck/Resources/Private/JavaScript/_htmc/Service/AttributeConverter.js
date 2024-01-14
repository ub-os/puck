import { jsonParse } from "../Utility/StringUtility"

export default class AttributeConverter {
    attributes = {}
    constructor(attributes) {
        this.attributes = attributes
    }
    write(attrKey, val) {
        switch (typeof this.attributes[attrKey] ?? 'default') {
            case 'boolean':
                return val ? '' : 'false'
            case 'object':
                return JSON.stringify(val)
            case 'string':
                return val
            default:
                return val.toString()
        }
    }
    read(attrKey, val) {
        switch (typeof this.attributes[attrKey] ?? 'default') {
            case 'boolean':
                return val !== '0' && val !== 'false'
            case 'object':
                return jsonParse(val)
            case 'number':
                return Number(val.replace(/_/g, ""))
            default:
                return val
        }
    }
}