import fs from 'fs';
import { createRequire } from 'module';
import sassVars from 'get-sass-vars';

const require = createRequire(import.meta.url);

/*const sassVariablesFile = fs.readFile('./theme/Resources/Private/Stylesheets/00-settings/_variables.sass', 'utf8', async (err, data) => {
    if (err) {
        console.error(err)
        return
    }
    console.log(data)
    const string = `$banana: green\n`
    const json = await sassVars(string, {

        sassOptions: {
            indentedSyntax: true,
        }
    });
    console.log(json);
    //console.log(data)
});*/

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