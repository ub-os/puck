import browserify from 'browserify';
import babelify from 'babelify';
import fs from 'fs';
import path from 'path';
import { minify } from 'uglify-js';
import exorcist from 'exorcist';

const sourcePath = './puck/Resources/Private/JavaScript/';
const distPath = './puck/Resources/Public/JavaScript/';
const fileNames = [
    'app',
    'web-layout-remember-scroll-pos',
];

const args = process.argv.slice(2);
const options = {
    uglify: args.includes('uglify'),
};

ensureDirectoryExistence(distPath);
for (let fileName of fileNames) {
    renderFile(fileName);
}

function renderFile(fileName) {
    const srcFilePath = sourcePath + fileName + '.js';
    const distFilePath = distPath + fileName + '.js';
    const minifiedDistFilePath = distPath + fileName + '.min.js';
    const sourceMapFilePath = distPath + fileName + '.js.map';

    const b = browserify(srcFilePath, { debug: true })
        .transform(babelify, {
            presets: ['@babel/preset-env'],
            plugins: [
                ['@babel/plugin-transform-react-jsx', { pragma: 'jsx' }],
                ['@babel/plugin-proposal-decorators', { version: '2023-01' }],
            ],
            comments: false,
        });

    const bundleStream = b.bundle()
        .pipe(exorcist(sourceMapFilePath)) // Pipe the bundle through exorcist to generate the source map file
        .pipe(fs.createWriteStream(distFilePath));

    bundleStream.on('close', () => {
        console.log('Building ' + distFilePath);
        console.log('Bundling complete!');

        if (options.uglify) {
            fs.readFile(distFilePath, 'utf8', (err, buf) => {
                if (err) {
                    console.error('Error reading bundled file:', err);
                    process.exit(1);
                }

                const result = minify(buf, { compress: true, mangle: true });
                fs.writeFile(minifiedDistFilePath, result.code, 'utf8', (err) => {
                    if (err) {
                        console.error('Error writing minified file:', err);
                        process.exit(1);
                    }
                    console.log('Minification complete!');
                });
            });
        }
    });

    bundleStream.on('error', (err) => {
        console.error('Error bundling:', err);
        process.exit(1);
    });
}

function ensureDirectoryExistence(filePath) {
    const dirname = path.dirname(filePath);
    if (fs.existsSync(dirname)) {
        return true;
    }
    ensureDirectoryExistence(dirname);
    fs.mkdirSync(dirname);
}
