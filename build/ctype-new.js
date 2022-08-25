const fs = require('fs');
const contentPath = 'theme/Configuration/_Content';
const args = process.argv.slice(2);
const ctype = args[0];
const camelCase = args[1];
let options = {};
if (args[2]) {
    for (let o of args[2].split('+')) {
        let option = o.split('=');
        options[option[0]] = option[1];
    }
}
let showitem = `--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;frames,
        --palette--;;headers,`;
if (options.showitem) {
    for (let palette of options.showitem.split(',')) {
        if (palette === 'image') {
            showitem += `
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.images,
                --palette--;;${palette},`;
        } else {
            showitem += `\n\t\t--palette--;;${palette},`;
        }
    }
}
let dataProcessing = '';
if (options.dataProcessing) {
    dataProcessing += 'dataProcessing {';
    for (let [i, processor] of options.dataProcessing.split(',').entries()) {
        if (processor === 'inline_textmedia') {
            dataProcessing += `
        ${i+1}0 = TYPO3\\CMS\\Frontend\\DataProcessing\\DatabaseQueryProcessor
        ${i+1}0 {
            table = tx_theme_domain_model_inline_textmedia
            pidInList.field = pid
            where {
                data = field:uid
                intval = 1
                wrap = parent_uid=|
            }
            orderBy = sorting
            as = items
            dataProcessing {
                10 = TYPO3\\CMS\\Frontend\\DataProcessing\\FilesProcessor
                10 {
                    references.fieldName = assets
                }
            }
        }`;
        }
        if (processor === 'assets') {
            dataProcessing += `
        ${i+1}0 = TYPO3\\CMS\\Frontend\\DataProcessing\\FilesProcessor
        ${i+1}0 {
            references.fieldName = assets
        }`;
        }
    }
    dataProcessing += '\n\t}';
}
if (ctype.length > 1) {
    fs.mkdirSync(`${contentPath}/${camelCase}`, { recursive: true }, (err) => {if (err) throw err;});
    fs.writeFile(`${contentPath}/${camelCase}/${camelCase}.html`, '', function(){});
    fs.writeFile(`${contentPath}/${camelCase}/${camelCase}.tsconfig`, `TCEFORM.tt_content {
}`, function(){});
    fs.writeFile(`${contentPath}/${camelCase}/${camelCase}.typoscript`, `tt_content.${ctype} {
    ${dataProcessing}
}`, function(){});
    fs.writeFile(`${contentPath}/${camelCase}/${camelCase}TCA.php`, `<?php
$ctype = '${ctype}';
$GLOBALS['TCA']['tt_content']['types'][$ctype]['showitem'] = '
    ${showitem}
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
        rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
';
$GLOBALS['TCA']['tt_content']['types'][$ctype]['columnsOverrides'] = [];`, function(){});
    console.log(`CType "${ctype}" created in ${contentPath}/${camelCase}.`);
    const configs = require('./ctype-configs');
} else {
    console.log('ctype and/or ctype camelcase missing!');
}