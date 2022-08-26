import fs from 'fs';
import {generateFonts} from "fantasticon";

const resourcePath = 'theme/Resources/Public/';
if (!fs.existsSync(resourcePath+"fonts/icons")){
    fs.mkdirSync(resourcePath+"fonts/icons", { recursive: true });
}
generateFonts({
    inputDir: `${resourcePath}images/icons`, // (required)
    outputDir: `${resourcePath}fonts/icons`, // (required)
    assetTypes: ['scss', 'json', 'html', 'css'],
    fontTypes: ['ttf', 'woff', 'woff2'],
    fontsUrl: '../fonts/icons',
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
    },
    pathOptions: {
        scss: `theme/Resources/Private/Stylesheets/00-settings/_icon-font.scss`
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
}).then(results => console.log(results.assetsOut.json));
