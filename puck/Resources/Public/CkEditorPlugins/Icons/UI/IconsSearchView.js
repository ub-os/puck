
import { UI, Utils } from "@typo3/ckeditor5-bundle.js";

export default class IconsSearchView extends UI.FormHeaderView {

    constructor(locale) {
        super(locale);
        const t = locale.t;
        this.set('class', 'ck-icons-navigation');
        this.searchInputView = this._createSearchInput( 'Search' );
        this.searchInputView.set('class', 'ck-icons-search');
        this.children.add(this.searchInputView);
    }

    focus() {
        this.searchInputView.focus();
    }
    _createSearchInput( label ) {
        const input = new UI.LabeledFieldView( this.locale, UI.createLabeledInputText );
        input.label = label;
        input.fieldView.on('input', evt => {
            input.fieldView.value = evt.source.element.value;
        });
        input.fieldView.delegate('change:value').to(this);
        return input;
    }
}