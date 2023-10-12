import sass from 'sass';
import globImporter from 'node-sass-glob-importer';
import fs from 'fs';
import path from 'path';

const sourcePath = "./puck/Resources/Private/Stylesheets/";
const distPath = "./puck/Resources/Public/Css/";
const fileNames = [
  "styles",
  "be-ck-contents",
  "backend/general"
];

ensureDirectoryExistence(distPath);
for (let fileName of fileNames) {
    renderFile(fileName);
}

function renderFile(fileName) {
    const sassFile = sourcePath+fileName+".sass";
    const cssFile = distPath+fileName+".css";

    sass.render({
        file: sassFile,
        importer: globImporter(),
        outFile: cssFile,
        sourceMap: true,
        quietDeps: true,
    }, function(error, result) {
        console.log('Building ' + cssFile);
        if(!error){
            fs.mkdirSync(path.dirname(cssFile), {recursive: true})
            fs.writeFile(cssFile, result.css, function(err){
                if(!err) {
                    console.log('Building complete!');
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