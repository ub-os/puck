const svgstore = require('svgstore');
const fs = require('fs');

const iconPath = 'theme/Resources/Public/images/icons';
const distPath = 'theme/Resources/Public/images';
const storeName = 'sprite-sheet.svg';

function getIcons(path) {
    return fs.readdirSync(path).filter(function (file) {
        return file != storeName;
    });
}
const icons = getIcons(iconPath);
console.log(icons);
const sprites = svgstore();
for (let ic of icons) {
    sprites.add(ic.replace('.svg', '').toLowerCase(), fs.readFileSync(iconPath+'/'+ic, 'utf8'));
}
fs.writeFileSync(distPath+'/'+storeName, sprites);

