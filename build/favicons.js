import fs from 'fs';
import favicons from 'favicons';

const resourcePath = 'puck/Resources/Public/';
const distPath = `${resourcePath}Icons/Favicons/packages/`;
const sourcePath = `${resourcePath}Icons/Favicons/`;
const sourceFileNames = [];

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
        };
    favicons(sourcePath, configuration).then((response,error) => {
        callback(response);
    });
}

fs.readdirSync(sourcePath).forEach(file => {
    if (file.includes('.svg')) {
        sourceFileNames.push(file.replace('.svg', ''));
    }
});
for (let sourceFileName of sourceFileNames) {
    generateFavicons(`${sourcePath}${sourceFileName}.svg`, `${distPath}${sourceFileName}/`, sourceFileName);
}
