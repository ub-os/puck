
import {Core, Typing, UI, Utils, CKEditor5Plugins, Widget} from "@typo3/ckeditor5-bundle.js";
import IconsView from './UI/IconsView.js';
import IconsNavigationView from './UI/IconsNavigationView.js';
import IconsSearchView from './UI/IconsSearchView.js';
import IconGridView from './UI/IconGridView.js';
import IconInfoView from './UI/IconInfoView.js';

const ALL_SPECIAL_CHARACTERS_GROUP = 'All';
const ICON_TAG_CLASS = 'ckeditor-icon-tag';
const ICON_TAG = 'span';
const toolbarIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M500.3 7.3C507.7 13.3 512 22.4 512 32V176c0 26.5-28.7 48-64 48s-64-21.5-64-48s28.7-48 64-48V71L352 90.2V208c0 26.5-28.7 48-64 48s-64-21.5-64-48s28.7-48 64-48V64c0-15.3 10.8-28.4 25.7-31.4l160-32c9.4-1.9 19.1 .6 26.6 6.6zM74.7 304l11.8-17.8c5.9-8.9 15.9-14.2 26.6-14.2h61.7c10.7 0 20.7 5.3 26.6 14.2L213.3 304H240c26.5 0 48 21.5 48 48V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V352c0-26.5 21.5-48 48-48H74.7zM192 408a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM478.7 278.3L440.3 368H496c6.7 0 12.6 4.1 15 10.4s.6 13.3-4.4 17.7l-128 112c-5.6 4.9-13.9 5.3-19.9 .9s-8.2-12.4-5.3-19.2L391.7 400H336c-6.7 0-12.6-4.1-15-10.4s-.6-13.3 4.4-17.7l128-112c5.6-4.9 13.9-5.3 19.9-.9s8.2 12.4 5.3 19.2zm-339-59.2c-6.5 6.5-17 6.5-23 0L19.9 119.2c-28-29-26.5-76.9 5-103.9c27-23.5 68.4-19 93.4 6.5l10 10.5 9.5-10.5c25-25.5 65.9-30 93.9-6.5c31 27 32.5 74.9 4.5 103.9l-96.4 99.9z"/></svg>';

export default class Icons extends Core.Plugin {

    static get pluginName() {
        return 'Icons';
    }

    constructor(editor) {
        super(editor);
        const t = editor.t;
        this._icons = new Map();
        this._groups = new Map();
        this._allIconsGroupLabel = t('All');
    }

    init() {
        const editor = this.editor;
        const model = editor.model;
        const editing = editor.editing;
        const t = editor.t;
        const inputCommand = editor.commands.get('insertText');
        const allowAttributes = [
            ...model.schema.getDefinition('$text').allowAttributes,
            'class',
            'htmlA',
        ];
        const styles = [
            { tag: 'strong', attribute: 'bold' },
            { tag: 'i', attribute: 'italic' },
            { tag: 'u', attribute: 'underline' },
            { tag: 's', attribute: 'strikethrough' },
            { tag: 'sup', attribute: 'superscript' },
            { tag: 'sub', attribute: 'subscript' },
        ];
        model.schema.register('iconTag', {
            allowWhere: '$text',
            isInline: true,
            isObject: false,
            allowAttributes
        });
        editor.conversion.for('upcast')
            .elementToElement({
                //upcastAlso: ['a', 'sup', 'u', 'sub'],
                view: {
                    name: ICON_TAG,
                    classes: [ ICON_TAG_CLASS ]
                },
                model: 'iconTag',
            })
            .attributeToAttribute( {
                view: 'class',
                model: 'class',
            } );
        editor.conversion.for('dataDowncast')
            .elementToElement({
                //downcastAlso: ['a', 'sup', 'u', 'sub'],
                model: 'iconTag',
                view: (modelElement, {writer}) => {
                    console.log('dataDowncast');
                    let iconTag = writer.createAttributeElement(ICON_TAG, {
                        class: modelElement._attrs.get('class'),
                        underline: modelElement.getAttribute('underline'),
                    } );
                    console.log(iconTag);
                    for (const style of styles) {
                        if (modelElement.getAttribute(style.attribute)) {
                            //const styledElement = writer.createContainerElement(style.tag);
                            //writer.insert(writer.createPositionAt(styledElement, 0), iconTag);
                            //iconTag = styledElement;
                        }
                    }
                    console.log(iconTag);
                    return iconTag;
                },
                converterPriority: 'highest'
            })
        ;
        editor.conversion.for('editingDowncast')
            .elementToElement({
                //downcastAlso: ['a', 'sup', 'u', 'sub'],
                model: 'iconTag',
                view: (modelElement, {writer}) => {
                    console.log('editingDowncast');
                    console.log(modelElement);
                    return Widget.toWidget(
                        writer.createContainerElement(
                            'span',
                            { class: 'ck-icon-editing__widget' },
                            [writer.createRawElement(
                                'span',
                                {}, element => {
                                    return element.innerHTML = `<${ICON_TAG} class="${modelElement._attrs.get('class')}"></${ICON_TAG}>`
                                }
                                )
                            ]
                        ),
                        writer,
                        { label: 'icon widget' }
                    );
                },
            });

// Downcast converter: model -> view
/*
        editor.conversion.for('downcast').add(dispatcher => {
            dispatcher.on('insert:iconTag', (evt, data, conversionApi) => {
                const modelElement = data.item;
                const viewWriter = conversionApi.writer;

                // Create the base iconTag
                let iconTag = viewWriter.createAttributeElement('span', {class: modelElement._attrs.get('class')});

                // Wrap the iconTag with each style that is applied
                for (const style of styles) {
                    if (modelElement.getAttribute(style.attribute)) {
                        const styledElement = viewWriter.createContainerElement(style.tag);
                        viewWriter.insert(viewWriter.createPositionAt(styledElement, 0), iconTag);
                        iconTag = styledElement;
                    }
                }

                conversionApi.mapper.bindElements(modelElement, iconTag);
                conversionApi.writer.insert(conversionApi.mapper.toViewPosition(data.range.start), iconTag);
                evt.stop();
            }, { converterPriority: 'high' }); // Set converter priority
        });
*/


        for (const style of styles) {
            //this.createStyleConverter(style.tag, style.attribute)(editor);
        }

        // Add the `specialCharacters` dropdown button to feature components.
        editor.ui.componentFactory.add('icons', locale => {
            const dropdownView = UI.createDropdown(locale);
            let dropdownPanelContent;
            dropdownView.buttonView.set({
                label: 'Icons',
                icon: toolbarIcon,
                tooltip: true
            });

            dropdownView.bind('isEnabled').to(inputCommand);
            // Insert a special character when a tile was clicked.
            dropdownView.on('execute', (evt, icon) => {
                model.change(writer => {
                    const insertPosition = editor.model.document.selection.getFirstPosition();
                    const classes = `${icon.baseClass} ${icon.keyClassPrefix}${icon.keyClass} ${ICON_TAG_CLASS}`;
                    let iconTag = writer.createElement('iconTag', { class: classes });
                    model.insertContent(iconTag);
                    if (insertPosition.nodeBefore && insertPosition.nodeBefore.constructor.name === 'Text$1') {
                        const attrs = insertPosition.nodeBefore._attrs;
                        attrs.set('class', classes);
                        iconTag = writer.createElement('iconTag', attrs);
                        writer.remove( insertPosition.nodeAfter );
                        console.log(iconTag);
                        model.insertContent(iconTag);
                    }
                    editing.view.focus();
                    writer.setSelection(iconTag, 'on');

                });
            });
            dropdownView.on('change:isOpen', () => {
                if (!dropdownPanelContent) {
                    dropdownPanelContent = this._createDropdownPanelContent(locale, dropdownView);
                    const iconsView = new IconsView(locale, dropdownPanelContent.navigationView, dropdownPanelContent.searchView, dropdownPanelContent.gridView, dropdownPanelContent.infoView);
                    dropdownView.panelView.children.add(iconsView);
                }
                dropdownPanelContent.infoView.set({
                    character: null,
                    name: null
                });
            });
            return dropdownView;
        });
    }

    createStyleConverter(styleTag, styleAttribute) {
        return function(editor) {
            // Upcast converter: view -> model
            editor.conversion.for('upcast').elementToElement({
                view: {
                    name: styleTag,
                    classes: [ ICON_TAG_CLASS ]
                },
                model: (viewElement, modelWriter) => {
                    return modelWriter.createElement('iconTag', { [styleAttribute]: true });
                }
            });

            // Downcast converter: model -> view
            editor.conversion.for('downcast').add(dispatcher => {
                dispatcher.on(`insert:iconTag`, (evt, data, conversionApi) => {
                    const modelElement = data.item;

                    if (modelElement.getAttribute(styleAttribute)) {
                        const viewWriter = conversionApi.writer;
                        const iconTag = viewWriter.createAttributeElement('span', {class: modelElement._attrs.get('class')});
                        const styledElement = viewWriter.createContainerElement(styleTag);
                        viewWriter.insert(viewWriter.createPositionAt(styledElement, 0), iconTag);
                        conversionApi.mapper.bindElements(modelElement, styledElement);
                        conversionApi.mapper.bindElements(modelElement, iconTag);
                        conversionApi.writer.insert(conversionApi.mapper.toViewPosition(data.range.start), styledElement);
                        evt.stop();
                    }
                }, { converterPriority: 'high' }); // Set converter priority
            });
        }
    }

    /**
     * Adds a collection of special characters to the specified group. The title of a special character must be unique.
     *
     * **Note:** The "All" category name is reserved by the plugin and cannot be used as a new name for a special
     * characters category.
     */
    addItems(groupName, items, options = { label: groupName }) {
        if (groupName === ALL_SPECIAL_CHARACTERS_GROUP) {
            /**
             * The name "All" for a special category group cannot be used because it is a special category that displays all
             * available special characters.
             *
             * @error special-character-invalid-group-name
             */
            throw new Utils.CKEditorError('special-character-invalid-group-name', null);
        }
        const group = this._getGroup(groupName, options.label);
        for (const item of items) {
            group.items.add(item.key);
            this._icons.set(item.key, {
                title: item.title,
                keyClass: item.key,
                baseClass: item.baseClass || '',
                keyClassPrefix: item.keyClassPrefix || 'icon--'
            });
        }
    }
    /**
     * Returns special character groups in an order determined based on configuration and registration sequence.
     */
    getGroups() {
        const groups = Array.from(this._groups.keys());
/*        const order = this.editor.config.get('icons.order') || [];
        const invalidGroup = order.find(item => !groups.includes(item));
        if (invalidGroup) {
            /!**
             * One of the special character groups in the "specialCharacters.order" configuration doesn't exist.
             *
             * @error special-character-invalid-order-group-name
             *!/
            throw new Utils.CKEditorError('special-character-invalid-order-group-name', null, { invalidGroup });
        }*/
        return new Set([
            //...order,
            ...groups.sort()
        ]);
    }
    /**
     * Returns a collection of special characters symbol names (titles).
     */
    getIconsForGroup(groupName) {
        if (groupName === ALL_SPECIAL_CHARACTERS_GROUP) {
            return new Set(this._icons.keys());
        }
        const group = this._groups.get(groupName);
        if (group) {
            return group.items;
        }
    }
    /**
     * Returns the symbol of a special character for the specified name. If the special character could not be found, `undefined`
     * is returned.
     *
     * @param title The title of a special character.
     */
    getIcon(title) {
        return this._icons.get(title);
    }
    /**
     * Returns a group of special characters. If the group with the specified name does not exist, it will be created.
     *
     * @param groupName The name of the group to create.
     * @param label The label describing the new group.
     */
    _getGroup(groupName, label) {
        if (!this._groups.has(groupName)) {
            this._groups.set(groupName, {
                items: new Set(),
                label,
            });
        }
        return this._groups.get(groupName);
    }
    /**
     * Updates the symbol grid depending on the currently selected character group.
     */
    _updateGrid(currentGroupName, gridView) {
        // Updating the grid starts with removing all tiles belonging to the old group.
        gridView.tiles.clear();
        const iconTiles = this.getIconsForGroup(currentGroupName);
        for (const title of iconTiles) {
            const icon = this.getIcon(title);
            gridView.tiles.add(gridView.createTile(icon));
        }
    }
    _filterGrid(filterString, gridView) {
        for (const tile of gridView.tiles) {
            if (tile.ariaLabel.indexOf(filterString) === -1) {
                tile.element.style.display = 'none';
            } else {
                tile.element.style.display = '';
            }
        }
    }
    /**
     * Initializes the dropdown, used for lazy loading.
     *
     * @returns An object with `navigationView`, `gridView` and `infoView` properties, containing UI parts.
     */
    _createDropdownPanelContent(locale, dropdownView) {
        const groupEntries = Array
            .from(this.getGroups())
            .map(name => ([name, this._groups.get(name).label]));
        // The map contains a name of category (an identifier) and its label (a translational string).
        const specialCharsGroups = new Map([
            // Add a special group that shows all available special characters.
            [ALL_SPECIAL_CHARACTERS_GROUP, this._allIconsGroupLabel],
            ...groupEntries
        ]);
        const navigationView = new IconsNavigationView(locale, specialCharsGroups);
        const searchView = new IconsSearchView(locale);
        const gridView = new IconGridView(locale);
        const infoView = new IconInfoView(locale);
        gridView.delegate('execute').to(dropdownView);
        gridView.on('tileHover', (evt, icon) => {
            infoView.set(icon);
        });
        gridView.on('tileFocus', (evt, icon) => {
            infoView.set(icon);
        });
        // Update the grid of special characters when a user changed the character group.
        navigationView.on('execute', () => {
            this._updateGrid(navigationView.currentGroupName, gridView);
        });
        searchView.on('change:value', evt => {
            window.requestAnimationFrame(() => {
                this._filterGrid(evt.source.value, gridView);
            });
        });
        // Set the initial content of the special characters grid.
        this._updateGrid(navigationView.currentGroupName, gridView);
        return { navigationView, searchView, gridView, infoView };
    }
}