
import { UI } from "@typo3/ckeditor5-bundle.js";

export default class IconInfoView extends UI.View {
    constructor(locale) {
        super(locale);
        const bind = this.bindTemplate;
        this.set('title', null);
        this.set('key', null);
        this.setTemplate({
            tag: 'div',
            children: [
                {
                    tag: 'span',
                    attributes: {
                        class: [
                            'ck-icon-info__name'
                        ]
                    },
                    children: [
                        {
                            // Note: ZWSP to prevent vertical collapsing.
                            text: bind.to('title', title => title ? title : '\u200B')
                        }
                    ]
                },
                {
                    tag: 'span',
                    attributes: {
                        class: [
                            'ck-icon-info__code'
                        ]
                    },
                    children: [
/*                        {
                            tag: 'span',
                            attributes: {
                                class: [
                                    'char-icon',
                                    bind.to('key', key => key ? `icon--${this.key}` : 'icon--null')
                                ]
                            }
                        }*/
                    ]
                }
            ],
            attributes: {
                class: [
                    'ck',
                    'ck-icon-info'
                ]
            }
        });
    }
}