

// Prerequisites: class that takes a element (or selector) and options in the constructor and has a mount method
// Automatic creation of instances with selector and options from data attribute
export default function Component(target, { kind, name }) {
    if (kind === 'class' && target.prototype.mount) {
        const getOptions = (str, options) => {
            try {
                return {...JSON.parse(str), ...options}
            } catch (e) {
                return str
            }
        }
        const newClass = class extends target {
            static createInstancesFromDataAttribute(
                root = document,
                attribute = 'data-' + name.replace(/([a-z0–9])([A-Z])/g, "$1-$2").toLowerCase(),
                options = {},
            ) {
                //console.log({label:`create instances for ${name}`, root, attribute})
                return new Map([...root.querySelectorAll(`[${attribute}]`)].map((element, i) =>  [
                       element.id || i,
                       new target(
                           element,
                           getOptions(element.getAttribute(`${attribute}`) || '{}', options)
                       ).mount()
                    ]
                ))
            }
        }
        return newClass
    }
}



