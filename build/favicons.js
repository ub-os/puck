import fs from 'fs';
import favicons from 'favicons';

function generateFavicons(sourcePath, distPath, fileName) {
    const
        configuration =     {
            path: distPath,
            lang: 'de-DE',
            start_url: '/',
            icons: {
                android: true,
                appleIcon: true,
                appleStartup: false,
                favicons: true,
                windows: false,
                yandex: false,
            }
        },
        callback = function (response, error) {
            if (error) {
                console.log(error.message); // Error description e.g. "An unknown error has occurred"
                return;
            }
            if (!fs.existsSync(distPath)){
                fs.mkdirSync(distPath, { recursive: true });
            }
            for (let file of response.images.concat(response.files)) {
                fs.writeFile(`${distPath+file.name}`, file.contents, function(){});
            }
            let html = '';
            for (let icon of response.html) {
                html += icon+'' +
                    '';
            }
            fs.writeFile('puck/Configuration/Typoscript/09_favicons.typoscript', `
page.headerData.99999999 = TEXT
page.headerData.99999999 {
    value = ${ html.replaceAll('/' + distPath, '{path: EXT:'+distPath.replace(fileName, '{$favicon}')).replace(/"{path: EXT:([^"]+)"/g, '"{path: EXT:$1}"') }
    insertData = 1
}`,function(){});
        };
    favicons(sourcePath, configuration).then((response,error) => {
        callback(response);
    });
}

const resourcePath = 'puck/Resources/Public/';
const distPath = `${resourcePath}Icons/Favicons/packages/`;
const sourcesPath = `${resourcePath}Icons/Favicons/`;
const sourceFileNames = [];
fs.readdirSync(sourcesPath).forEach(file => {
    if (file.includes('.svg')) {
        sourceFileNames.push(file.replace('.svg', ''));
    }
});
for (let sourceFileName of sourceFileNames) {
    generateFavicons(`${sourcesPath}${sourceFileName}.svg`, `${distPath}${sourceFileName}/`, sourceFileName);
}
