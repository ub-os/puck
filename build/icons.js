var fs = require('fs');
var themeIcons = require('../theme/Resources/Public/fonts/icons/icons.json');
var ckIconPluginDialogFile = './theme/Resources/Public/CkEditorPlugins/insertIcon/dialog.js';
fs.readFile(ckIconPluginDialogFile, 'utf8', function (err,data) {
    if (err) {
        return console.log(err);
    }
    var result = data.replace(/var themeCustom = {.*};+/g, 'var themeCustom = '+JSON.stringify(themeIcons)+';');
    fs.writeFile(ckIconPluginDialogFile, result, 'utf8', function (err) {
        if (err) return console.log(err);
    });
});