import fs from 'fs';
import svgstore from 'svgstore';

const iconPath = 'puck/Resources/Public/Icons/Frontend';
const distPath = 'puck/Resources/Public/Icons';
const storeName = 'sprite-sheet.svg';

function getIcons(path) {
    return fs.readdirSync(path).filter(function (file) {
        return file != storeName;
    });
}
const icons = getIcons(iconPath);
const sprites = svgstore();
for (let ic of icons) {
    sprites.add(ic.replace('.svg', '').toLowerCase(), fs.readFileSync(iconPath+'/'+ic, 'utf8'));
}
fs.writeFileSync(distPath+'/'+storeName, sprites);

