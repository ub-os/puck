import fs from 'fs';
import favicons from 'favicons';

const resourcePath = 'puck/Resources/Public/',
    distPath = `${resourcePath}Icons/Favicons/`,
    source = `${resourcePath}Icons/website-favicon.svg`,
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
        fs.writeFile('puck/Configuration/Typoscript/05_favicons.typoscript', `
page.headerData.99999999 = TEXT
page.headerData.99999999.value (
${html.replaceAll(distPath, 'typo3conf/ext/'+distPath)}
)`,function(){});
    };

favicons(source, configuration).then((response,error) => {
   callback(response);
});