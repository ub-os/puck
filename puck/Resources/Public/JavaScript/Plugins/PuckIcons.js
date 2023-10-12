import { Plugin } from "@ckeditor/ckeditor5-core";
import ICONS_CODEPOINTS from '../../Fonts/Icons/icons.js';

const iconGroupName = 'Puck';
const baseClass = 'char-icon';
const keyClassPrefix = 'icon--';

export default class PuckIcons extends Plugin {

    static pluginName = 'PuckIcons';
    init() {
        this.addIcons(this.mapIconData(ICONS_CODEPOINTS));
    }
    addIcons(icons) {
        const plugin = this.editor.plugins.get( 'Icons' );
        plugin.addItems(
            iconGroupName,
            [...icons],
            { label: iconGroupName }
        );
    }
    mapIconData(data) {
        return Object.keys(data).map(key => {
            return {
                key,
                title: key.replaceAll('-', ' '),
                baseClass: baseClass,
                keyClassPrefix: keyClassPrefix,
            }
        });
    }
}