import { jsonParse } from "~/Utility/StringUtility.js";

export default class AttributeConverter {
    constructor(props) {
        this.props = props
    }
    write(propName, val) {
        switch (typeof this.props[propName] ?? 'default') {
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
    read(propName, val) {
        switch (typeof this.props[propName] ?? 'default') {
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