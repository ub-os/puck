import fs from 'fs';
import xliff from 'xliff';
import path from 'path';

const extensions = [
    'theme'
];
function ensureDirectoryExistence(filePath) {
    var dirname = path.dirname(filePath);
    if (fs.existsSync(dirname)) {
        return true;
    }
    ensureDirectoryExistence(dirname);
    fs.mkdirSync(dirname);
}
function writeJson(xlf, jsonPath) {
    xliff.xliff12ToJs(xlf, (err, res) => {
        if (err) {
            throw err;
        }
        ensureDirectoryExistence(jsonPath);
        fs.writeFileSync(jsonPath, JSON.stringify(res));
    });
}
for (let ext of extensions) {
    const xlfPath = `${ext}/Resources/Private/Language/`;
    const jsonPath = `${ext}/Resources/Public/json/language/`;
    const xlfFiles = fs.readdirSync(xlfPath);
    const xlfData = [];
    for (let file of xlfFiles) {
        if (fs.lstatSync(xlfPath+file).isFile()) {
            writeJson(fs.readFileSync(xlfPath+file, 'utf8'), jsonPath+file+'.json');
        }
    }
}


