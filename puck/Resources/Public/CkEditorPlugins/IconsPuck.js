import { Core } from "@typo3/ckeditor5-bundle.js";
//import * as jsonData from '../Fonts/Icons/icons.json';

const jsonPath = '../Fonts/Icons/icons.json';
const arrayImport = await import(jsonPath, { assert: { type: "json" } });
const jsonData = arrayImport.default
const iconGroupName = 'Puck';
const baseClass = 'char-icon';
const keyClassPrefix = 'icon--';
export default class IconsPuck extends Core.Plugin {

    static pluginName = 'IconsPuck';
    init() {
        this.addIcons(this.mapIconData(jsonData));
    }

    addIcons(icons) {
        const plugin = this.editor.plugins.get( 'Icons' );
        plugin.addItems(
            iconGroupName,
            [...icons],
            { label: iconGroupName }
        );
    }

    mapIconData(jsonData) {
        return Object.keys(jsonData).map(key => {
            return {
                key,
                title: key.replaceAll('-', ' '),
                baseClass: baseClass,
                keyClassPrefix: keyClassPrefix,
            }
        });
    }
}