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
    get controllerTargets() {
        return $$('[data-controller-target]')
    }
    connectControllers() {
        this.controllerElements.forEach(el => {
            this.connectControllerElement(el)
        })
        this.controllerTargets.forEach(el => {
            this.connectControllerTarget(el)
        })
        MutationManager.addById(
            'body-controller-children-observer',
            document.body,
            mutations => {
                mutations.forEach(mutation => {
                    if (mutation.type !== 'childList') return
                    new Promise(resolve => {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType !== Node.ELEMENT_NODE) return
                            if (node.hasAttribute('data-controller')) this.connectControllerElement(node)
                            node.$$('[data-controller]').forEach(el => this.connectControllerElement(el))
                        })
                        mutation.removedNodes.forEach(node => {
                            if (node.nodeType !== Node.ELEMENT_NODE) return
                            if (node.hasAttribute('data-controller-target')) this.disconnectControllerTarget(node)
                            node.$$('[data-target-for]').forEach(el => this.disconnectControllerTarget(el))
                        })
                        resolve()
                    }).then(() => {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType !== Node.ELEMENT_NODE) return
                            if (node.hasAttribute('data-controller-target')) this.connectControllerTarget(node)
                            node.$$('[data-target-for]').forEach(el => this.connectControllerTarget(el))
                        })
                        mutation.removedNodes.forEach(node => {
                            if (node.nodeType !== Node.ELEMENT_NODE) return
                            if (node.hasAttribute('data-controller')) this.disconnectControllerElement(node)
                            node.$$('[data-controller]').forEach(el => this.disconnectControllerElement(el))
                        })
                    })

                })
            },
            { childList: true, subtree: true },
        )
    }
    disconnectControllers() {
        MutationManager.remove('body-controller-children-observer')
        this.controllerElements.forEach(el => {
            this.disconnectControllerElement(el)
        })
    }
    connectControllerTarget(targetEl) {
        targetEl.dataset.controllerTarget.split(' ').forEach(targetDefinition => {
            const [
                targetName,
                controllerName,
                controllerElId
            ] = targetDefinition.split('#').join('@').split('@')
            if (!controllerName || !targetName) return
            const controllerEl = controllerElId ? $id(controllerElId) : targetEl.closest(`[data-controller^="${controllerName}"], [data-controller*=" ${controllerName}"]`)
            if (!controllerEl) return
            const controller = controllerEl.controllerCollection.list.find(controller => controller.constructor.identifier === controllerName)
            if (!controller) return
            if (!controller[targetName + 'Targets']) {
                controller[targetName + 'Targets'] = []
            }
            controller[targetName + 'Targets'].push(targetEl)
            if (controller[targetName + 'Connected'] && typeof controller[targetName + 'Connected'] === 'function') {
                controller[targetName + 'Connected'](targetEl)
            }
        })
    }
    connectAriaControls(targetEl) {
        const controllerEl = $id(targetEl.getAttribute('aria-controls'))
        if (!controllerEl || !controllerEl.controllerCollection) return
        controllerEl.controllerCollection.list.forEach(controller => {
            if (!controller.ariaControlsTargets) {
                controller.ariaControlsTargets = []
            }
            controller.ariaControlsTargets.push(targetEl)
            if (controller.ariaControlsConnected && typeof controller.ariaControlsConnected === 'function') {
                controller.ariaControlsConnected(targetEl)
            }
        })
    }
    connectHashLinks(targetEl) {
        const controllerEl = $id(targetEl.hash.replace('#', '').split('?')[0])
        if (!controllerEl || !controllerEl.controllerCollection) return
        controllerEl.controllerCollection.list.forEach(controller => {
            if (!controller.hashLinkTargets) {
                controller.hashLinkTargets = []
            }
            controller.hashLinkTargets.push(targetEl)
            if (controller.hashLinkConnected && typeof controller.hashLinkConnected === 'function') {
                controller.hashLinkConnected(targetEl)
            }
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
                el.controllerCollection.attributeChangedCallback(
                    mutation.attributeName,
                    mutation.oldValue,
                    mutation.target.getAttribute(mutation.attributeName)
                )
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