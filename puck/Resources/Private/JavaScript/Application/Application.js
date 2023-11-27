import { $, $$, $id, jsx } from "~/Utility/DomUtility"
import { ObserverCollector, MutationManager } from "~/Service/ObserverCollector"
import ScrollbarWidth from "~/Service/ScrollbarWidth"
import ControllerCollection from "~/Application/ControllerCollection"
class Application {
    static #instance = null;
    static get inst() {
        if (!Application.#instance) {
            Application.#instance = new Application();
        }
        return Application.#instance
    }
    services = {
        observerCollector: ObserverCollector.inst,
        scrollbarWidth: new ScrollbarWidth({}).start()
    }
    get controllerElements() {
        return $$('[data-controller]')
    }
    connectControllers() {
        MutationManager.addById(
            'body-controller-children-observer',
            document.body,
            mutations => {
                mutations.forEach(mutation => {
                    if (mutation.type !== 'childList') return
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType !== Node.ELEMENT_NODE) return
                        if (node.hasAttribute('data-controller')) this.connectControllerElement(node)
                        node.$$('[data-controller]').forEach(el => this.connectControllerElement(el))
                    })
                    mutation.removedNodes.forEach(node => {
                        if (node.nodeType !== Node.ELEMENT_NODE) return
                        if (node.hasAttribute('data-controller')) this.disconnectControllerElement(node)
                        node.$$('[data-controller]').forEach(el => this.disconnectControllerElement(el))
                    })
                })
            },
            { childList: true, subtree: true },
        )
        this.controllerElements.forEach(el => {
            this.connectControllerElement(el)
        })
    }
    disconnectControllers() {
        MutationManager.remove('body-controller-children-observer')
        this.controllerElements.forEach(el => {
            this.disconnectControllerElement(el)
        })
    }
    connectControllerElement(el) {
        if (!el.controllerCollection) {
            el.controllerCollection = new ControllerCollection(el)
        }
        el.controllerCollection.connectedCallback()
        MutationManager.addById(`controller-attribute-observer-${el.id}`, el, mutations => {
            mutations.forEach(mutation => {
                if (mutation.type !== 'attributes') return
                el.controllerCollection.attributeChangedCallback(mutation.attributeName, mutation.oldValue, mutation.target.getAttribute(mutation.attributeName))
            })
        }, { attributes: true, attributeOldValue: true })
    }
    disconnectControllerElement(el) {
        el.controllerCollection?.disconnectedCallback()
    }
    reset() {
        this.services.observerCollector.reset()
        this.disconnectControllers()
    }

}

const App = Application.inst
export default App