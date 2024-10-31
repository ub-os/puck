import * as sass from 'sass';
import fs from 'fs';
import path from 'path';
import { pathToFileURL } from 'url';
import { globSync } from 'glob';

const sourcePath = "./puck/Resources/Private/Stylesheets/";
const distPath = "./puck/Resources/Public/Css/dist/";
const fileNames = [
    "puck",
    "puck-backend"
];

const globImporters = (symbol, path) => {
    if (!path.endsWith('/')) {
        path = path + '/';
    }
    if (path.startsWith('./')) {
        path = path.substring(2);
    }
    return [
        {
            findFileUrl(url) {
                if (!url.startsWith(symbol)) return null;
                return new URL(url.replace(symbol, ''), pathToFileURL(path));
            }
        },
        {
            canonicalize(url) {
                if (!url.startsWith(symbol)) return null;
                return new URL('glob:' + url.replace(symbol, ''));
            },
            load(canonicalUrl) {
                let contents = '';
                if (canonicalUrl.pathname.startsWith('/')) {
                    canonicalUrl.pathname = canonicalUrl.pathname.substring(1);
                }
                const depth = (canonicalUrl.pathname.match(/\//g) || []).length;
                globSync(path + canonicalUrl.pathname).forEach(file => {
                    const fileArr = file.split('/');
                    const filePath = fileArr.slice(fileArr.length - 1 - depth, fileArr.length).join('/');
                    contents += `@import "~${filePath}";\n`
                })
                return {
                    contents,
                    syntax: 'scss'
                };
            }
        }
    ]
};


ensureDirectoryExistence(distPath);
for (let fileName of fileNames) {
    await renderFile(fileName);
}

async function renderFile(fileName) {
    const sassFile = sourcePath+fileName+".sass";
    const cssFile = distPath+fileName+".css";
    await sass.compileAsync(sassFile, {
        importers: [
            ...globImporters('~', sourcePath)
        ],
        outFile: cssFile,
        quietDeps: true,
        sourceMap: true,
    }).then((result) => {
        console.log('Building ' + cssFile);
        fs.mkdirSync(path.dirname(cssFile), {recursive: true})
        fs.writeFile(cssFile, result.css, function(err){
            if(!err) {
                console.log('Building complete!');
            } else {
                console.log(err);
            }
        });
        //fs.writeFile(cssFile+".map", result.sourceMap, function(err){});
    })
}

function ensureDirectoryExistence(filePath) {
    var dirname = path.dirname(filePath);
    if (fs.existsSync(dirname)) {
        return true;
    }
    ensureDirectoryExistence(dirname);
    fs.mkdirSync(dirname);
}