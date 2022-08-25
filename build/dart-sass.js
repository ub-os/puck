const sass = require('sass');
const globImporter = require('node-sass-glob-importer');
const fs = require('fs');
const path = require('path');

const sourcePath = "./theme/Resources/Private/Stylesheets/";
const distPath = "./theme/Resources/Public/css/";

const files = [
  "styles",
  "be-ck-contents",
  "backend/general"
];

for (let file of files) {
    renderFile(file);
}

function renderFile(file) {
    const sassFile = sourcePath+file+".sass";
    const cssFile = distPath+file+".css";
    sass.render({
        file: sassFile,
        importer: globImporter(),
        outFile: cssFile,
        sourceMap: true
    }, function(error, result) {
        if(!error){
            ensureDirectoryExistence(cssFile);
            fs.writeFile(cssFile, result.css, function(err){
                if(!err){
                } else {
                    console.log(err);
                }
            });
            fs.writeFile(cssFile+".map", result.map, function(err){});
        } else {
            console.log(error);
        }
    });
}

function ensureDirectoryExistence(filePath) {
    var dirname = path.dirname(filePath);
    if (fs.existsSync(dirname)) {
        return true;
    }
    ensureDirectoryExistence(dirname);
    fs.mkdirSync(dirname);
}