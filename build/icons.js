import fs from 'fs';
import { createRequire } from 'module';
const require = createRequire(import.meta.url);

const themeIcons = require('../theme/Resources/Public/Fonts/Icons/icons.json');

console.log(themeIcons);
const ckIconPluginDialogFile = './theme/Resources/Public/CkEditorPlugins/insertIcon/dialog.js';
fs.readFile(ckIconPluginDialogFile, 'utf8', function (err,data) {
    if (err) {
        return console.log(err);
    }
    let result = data.replace(/var themeCustom = {.*};+/g, 'var themeCustom = '+JSON.stringify(themeIcons)+';');
    fs.writeFile(ckIconPluginDialogFile, result, 'utf8', function (err) {
        if (err) return console.log(err);
    });
});