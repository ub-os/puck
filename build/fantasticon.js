import fs from 'fs';
import {generateFonts} from "fantasticon";

const resourcePath = 'puck/Resources/Public/';
if (!fs.existsSync(resourcePath+"Fonts/Icons")){
    fs.mkdirSync(resourcePath+"Fonts/Icons", { recursive: true });
}
generateFonts({
    inputDir: `${resourcePath}Icons/Frontend`, // (required)
    outputDir: `${resourcePath}Fonts/Icons`, // (required)
    assetTypes: ['scss', 'json', 'html', 'css'],
    fontTypes: ['ttf', 'woff', 'woff2'],
    fontsUrl: '../../Fonts/Icons',
    prefix: 'icon-',
    tag: '*',
    formatOptions: {
        svg: {
            normalize: true,
            fontHeight: 1000,
            descent: 40,
            centervertically: true,
            centerhorizontally: true,
        }
    },
    templates: {
        scss: `build/templates/fantasticon/scss.hbs`
    },
    pathOptions: {
        scss: `puck/Resources/Private/Stylesheets/02-generic/_icon-font.scss`
    },
    codepoints: {
    },
    getIconId: ({
        basename, // `string` - Example: 'foo';
        relativeDirPath, // `string` - Example: 'sub/dir/foo.svg'
        absoluteFilePath, // `string` - Example: '/var/icons/sub/dir/foo.svg'
        relativeFilePath, // `string` - Example: 'foo.svg'
        index // `number` - Example: `0`
    }) => basename.toLowerCase()
}).then(results => {
    // create js file with codepoints
    const jsString = 'export default '+JSON.stringify(results.codepoints)+';'
    fs.writeFileSync(`${resourcePath}Fonts/Icons/icons.js`, jsString);
});
