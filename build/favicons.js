const fs = require('fs');
const favicons = require("favicons");

const resourcePath = 'theme/Resources/Public/',
    distPath = `${resourcePath}images/favicons/`,
    source = `${resourcePath}images/icons/website-favicon.svg`,
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
    callback = function (error, response) {
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
        fs.writeFile('theme/Configuration/Typoscript/03_favicons.typoscript', `
page.headerData.99999999 = TEXT
page.headerData.99999999.value (
${html}
)
        `,function(){});
    };

favicons.favicons(source, configuration, callback);