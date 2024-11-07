import { $, $$, jsx } from '~/Utility/DomUtility'
import Showable from "~/Aspects/Showable"

/**
 * Dialog Aspect
 * Should only be used on <dialog> elements
 */
export default class Dialog extends Showable {
    static attributes = {
        ...Showable.attributes,
        escHide: true,
        focusOnShow: true,
        focusOutHide: true,
        outClickHide: true,
    }

    /**
     * @return {HTMLDialogElement}
     */
    get el() { return super.el }
    onShow(event) {
        this.el.show()
        super.onShow(event);
    }
    onHide(event) {
        super.onHide(event)
        if (event.detail.transition) {
            setTimeout(() => this.el.close(), this.duration)
        } else {
            this.el.close()
        }
    }

    connected() {
        if (this.el.tagName !== 'DIALOG') throw new Error('Dialog Aspect should only be used on dialog elements')
        super.connected()
        // hacky way to make dialog exit animation work
        // firefox doesn't support display animation yet, so we have to disable the native dialog close
        this.handlerSet.add(this.el, 'cancel', event => event.preventDefault())
        return this
    }

    disconnected() {
        super.disconnected()
        this.handlerSet.clear()
    }
}