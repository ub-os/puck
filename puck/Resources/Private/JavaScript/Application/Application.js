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
    get ariaControls() {
        return $$('[aria-controls]')
    }
    get hashLinks() {
        return $$('a[href*="#"]')
    }
    connectControllers() {
        MutationManager.addById(
            'body-controller-children-observer',
            document.body,
            mutations => this.childListObserverControllerHandler(mutations),
            { childList: true, subtree: true },
        )
        this.controllerElements.forEach(el => this.connectControllerElement(el))
        this.controllerTargets.forEach(el => this.connectControllerTarget(el))
        //this.ariaControls.forEach(el => this.connectAriaControl(el))
        this.hashLinks.forEach(el => this.connectHashLink(el))
    }

    childListObserverControllerHandler(mutations) {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType !== Node.ELEMENT_NODE) return
                if (node.hasAttribute('data-controller')) this.connectControllerElement(node)
                node.$$('[data-controller]').forEach(el => this.connectControllerElement(el))
            })
            mutation.removedNodes.forEach(node => {
                if (node.nodeType !== Node.ELEMENT_NODE) return
                if (node.hasAttribute('data-controller-target')) this.disconnectControllerTarget(node)
                //if (node.hasAttribute('aria-controls')) this.disconnectAriaControl(node)
                if (node.hash) this.disconnectHashLink(node)
                node.$$('[data-controller-target]').forEach(el => this.disconnectControllerTarget(el))
                //node.$$('[aria-controls]').forEach(el => this.disconnectAriaControl(el))
                node.$$('[href*="#"]').forEach(el => this.disconnectHashLink(el))
            })
        })
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType !== Node.ELEMENT_NODE) return
                if (node.hasAttribute('data-controller-target')) this.connectControllerTarget(node)
                //if (node.hasAttribute('aria-controls')) this.connectAriaControl(node)
                if (node.hash) this.connectHashLink(node)
                node.$$('[data-controller-target]').forEach(el => this.connectControllerTarget(el))
                //node.$$('[aria-controls]').forEach(el => this.connectAriaControl(el))
                node.$$('[href*="#"]').forEach(el => this.connectHashLink(el))
            })
            mutation.removedNodes.forEach(node => {
                if (node.nodeType !== Node.ELEMENT_NODE) return
                if (node.hasAttribute('data-controller')) this.disconnectControllerElement(node)
                node.$$('[data-controller]').forEach(el => this.disconnectControllerElement(el))
            })
        })
    }

    disconnectControllers() {
        MutationManager.remove('body-controller-children-observer')
        this.controllerElements.forEach(el => {
            this.disconnectControllerElement(el)
        })
    }

    getControllerInformationForTarget(targetEl) {
        return targetEl.dataset.controllerTarget.split(' ').map(targetDefinition => {
            const [
                targetName,
                controllerName,
                controllerElId
            ] = targetDefinition.split('#').join('@').split('@')
            if (!controllerName || !targetName) return
            const controllerEl =
                controllerElId
                ? $id(controllerElId)
                : targetEl.closest(`[data-controller="${controllerName}"], [data-controller^="${controllerName} "], [data-controller*=" ${controllerName} "], [data-controller$=" ${controllerName}"]`)
            if (!controllerEl) return
            const controller = controllerEl.controllerCollection.list.find(controller => controller.constructor.identifier === controllerName)
            if (!controller) return
            return {
                targetName,
                controller
            }
        }).filter(Boolean)
    }

    connectControllerTarget(targetEl) {
        console.log('connecting target', targetEl)
        this.getControllerInformationForTarget(targetEl).forEach(information => {
            if (typeof information.controller[information.targetName + 'Connected'] === 'function') {
                information.controller[information.targetName + 'Connected'](targetEl)
            }
        })
    }
    connectAriaControl(targetEl) {
        console.log('connecting aria-control', targetEl)
        const controllerEl = $id(targetEl.getAttribute('aria-controls'))
        if (!controllerEl || !controllerEl.controllerCollection) return
        controllerEl.controllerCollection.list.forEach(controller => {
            if (typeof controller['ariaControlConnected'] === 'function') {
                controller['ariaControlConnected'](targetEl)
            }
        })
    }

    connectHashLink(targetEl) {
        console.log('connecting hash link', targetEl)
        const controllerEl = $id(targetEl.hash.replace('#', '').split('?')[0])
        if (!controllerEl || !controllerEl.controllerCollection) return
        controllerEl.controllerCollection.list.forEach(controller => {
            if (typeof controller['hashLinkConnected'] === 'function') {
                controller['hashLinkConnected'](targetEl)
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
    disconnectControllerTarget(targetEl) {
        this.getControllerInformationForTarget(targetEl).forEach(information => {
            if (typeof information.controller[information.targetName + 'Disconnected'] === 'function') {
                information.controller[information.targetName + 'Disconnected'](targetEl)
            }
        })
    }
    disconnectAriaControl(targetEl) {
        const controllerEl = $id(targetEl.getAttribute('aria-controls'))
        if (!controllerEl || !controllerEl.controllerCollection) return
        controllerEl.controllerCollection.list.forEach(controller => {
            if (typeof controller['ariaControlDisconnected'] === 'function') {
                controller['ariaControlDisconnected'](targetEl)
            }
        })
    }
    disconnectHashLink(targetEl) {
        const controllerEl = $id(targetEl.hash.replace('#', '').split('?')[0])
        if (!controllerEl || !controllerEl.controllerCollection) return
        controllerEl.controllerCollection.list.forEach(controller => {
            if (typeof controller['hashLinkDisconnected'] === 'function') {
                controller['hashLinkDisconnected'](targetEl)
            }
        })
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