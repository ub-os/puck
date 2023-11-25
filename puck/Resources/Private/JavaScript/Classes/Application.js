import { ObserverManager } from "~/Classes/ObserverManager"

class Application {
    static #instance = null;
    static get inst() {
        if (!Application.#instance) {
            Application.#instance = new Application();
        }
        return Application.#instance
    }
    managers = {}
    observerManager = ObserverManager.inst
}

const App = Application.inst
export default App