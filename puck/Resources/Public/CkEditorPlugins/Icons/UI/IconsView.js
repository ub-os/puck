import { UI, Utils } from "@typo3/ckeditor5-bundle.js";

export default class IconsView extends UI.View {

    constructor(locale, navigationView, searchView, gridView, infoView) {
        super(locale);
        this.navigationView = navigationView;
        this.searchView = searchView;
        this.gridView = gridView;
        this.infoView = infoView;
        this.items = this.createCollection();
        this.focusTracker = new Utils.FocusTracker();
        this.keystrokes = new Utils.KeystrokeHandler();
        this._focusCycler = new UI.FocusCycler({
            focusables: this.items,
            focusTracker: this.focusTracker,
            keystrokeHandler: this.keystrokes,
            actions: {
                focusPrevious: 'shift + tab',
                focusNext: 'tab'
            }
        });
        this.setTemplate({
            tag: 'div',
            children: [
                this.navigationView,
                this.searchView,
                this.gridView,
                this.infoView
            ],
            attributes: {
                // Avoid focus loss when the user clicks the area of the grid that is not a button.
                // https://github.com/ckeditor/ckeditor5/pull/12319#issuecomment-1231779819
                tabindex: '-1'
            }
        });
        this.items.add(this.navigationView.groupDropdownView.buttonView);
        this.items.add(this.gridView);
    }

    render() {
        super.render();
        this.focusTracker.add(this.navigationView.groupDropdownView.buttonView.element);
        this.focusTracker.add(this.gridView.element);
        // Start listening for the keystrokes coming from #element.
        this.keystrokes.listenTo(this.element);
    }

    destroy() {
        super.destroy();
        this.focusTracker.destroy();
        this.keystrokes.destroy();
    }
    focus() {
        this.searchView.focus();
    }
}