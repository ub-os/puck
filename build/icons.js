import fs from 'fs';
import { createRequire } from 'module';
import sassVars from 'get-sass-vars';

const require = createRequire(import.meta.url);

/*const sassVariablesFile = fs.readFile('./puck/Resources/Private/Stylesheets/00-settings/_variables.sass', 'utf8', async (err, data) => {
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

const puckIcons = require('../puck/Resources/Public/Fonts/Icons/icons.json');
