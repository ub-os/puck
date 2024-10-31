import * as sass from 'sass';
import fs from 'fs';
import path from 'path';
import { globSync } from 'glob';
import { Buffer } from 'buffer';

const sourcePath = "puck/Resources/Private/Stylesheets/";
const distPath = "puck/Resources/Public/Css/dist/";
const fileNames = [
    "puck",
    "puck-backend"
];

const globImporter = (path) => {
    if (!path.endsWith('/')) {
        path = path + '/';
    }
    if (path.startsWith('./')) {
        path = path.substring(2);
    }
    return {
        canonicalize(url) {
            if (!url.includes('*')) return null;
            return new URL('glob:' + url);
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
                contents += `@import "${filePath}";\n`
            })
            return {
                contents,
                syntax: 'scss'
            };
        }
    }
};

function renderFile(fileName) {
    const sassFile = sourcePath+fileName+".sass";
    const cssFile = distPath+fileName+".css";
    const result = sass.compile(sassFile, {
        importers: [
            globImporter(sourcePath)
        ],
        loadPaths: [sourcePath, 'node_modules/'],
        quietDeps: true,
        sourceMap: true,
    })
    console.log('Building ' + cssFile);
    fs.mkdirSync(path.dirname(cssFile), {recursive: true})
    result.sourceMap.sources = result.sourceMap.sources.map(source => {
        if (source.startsWith('file://')) {
            const pathParts = source.split('/');
            return pathParts[pathParts.length - 1];
        }
        return source;
    })
    const sm = JSON.stringify(result.sourceMap)
    const smBase64 = (Buffer.from(sm, 'utf8') || '').toString('base64')
    const smComment = '/*# sourceMappingURL=data:application/json;charset=utf-8;base64,' + smBase64 + ' */'
    const css = result.css.toString() + '\n'.repeat(2) + smComment
    fs.writeFile(cssFile, css, err => {
        if (err) return console.log(err);
        console.log('Building complete!');
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

ensureDirectoryExistence(distPath);

for (let fileName of fileNames) {
    renderFile(fileName);
}