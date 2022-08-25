const fs = require('fs');
const contentPath = 'theme/Configuration/_Content';
const excludedCtypes = ['list'];
if (!fs.existsSync(contentPath+"/_CTypes")){
    fs.mkdirSync(contentPath+"/_CTypes");
}
function getDirectories(path) {
    return fs.readdirSync(path).filter(function (file) {
        return fs.statSync(path+'/'+file).isDirectory();
    });
}
function getCTypes(path) {
    let array = getDirectories(path);
    for (let entry of ['Menu', '_CTypes']) {
        let index = array.indexOf(entry);
        if (index !== -1) {array.splice(index, 1);}
    }
    let result = array.map(x => [x.toLowerCase(),x]);
    result.unshift(['menu_pages', 'Menu'], ['menu_subpages', 'Menu']);
    return result;
}
function sortCTypes(ctypes) {
    console.log(ctypes);
    ctypes.sort(function(x, y) {
        if (x[0] === 'text') {
            return -1;
        }
        if (x[0].includes('text') && !y[0].includes('text')) {
            return -1;
        }
        if (!x[0].includes('menu') && y[0].includes('menu')) {
            return -1;
        }
        if (!x[0].includes('anchor') && y[0].includes('anchor')) {
            return -1;
        }
        return x[0].localeCompare(y[0]);
    });
    console.log(ctypes);

    return ctypes;
}
const ctypes = sortCTypes(getCTypes(contentPath));
let CTypesPHPArray = '[';
let CTypesTSConfig = '';
let CTypesTS = '';
for (let ctype of ctypes) {
    if (excludedCtypes.indexOf(ctype[0]) == -1) {
        CTypesPHPArray += `['${ctype[0]}','${ctype[1]}'],`;
        CTypesTSConfig += `
mod.wizards.newContentElement.wizardItems {
    01_content {
        elements {
            ${ctype[0]} {
                iconIdentifier = ${ctype[0]}
                title = LLL:EXT:theme/Resources/Private/Language/locallang_ctypes.xlf:${ctype[0]}_title
                description = LLL:EXT:theme/Resources/Private/Language/locallang_ctypes.xlf:${ctype[0]}_desc
                tt_content_defValues {
                    CType = ${ctype[0]}
                }
            }
        }
        show = *
    }
}
   `;
        CTypesTS += `
tt_content {
    ${ctype[0]} =< lib.contentElement
    ${ctype[0]} {
        templateName = ${ctype[1]}
        templateRootPaths.900 = EXT:${contentPath}/${ctype[1]}/
        partialRootPaths.900 = EXT:${contentPath}/${ctype[1]}/Partials/
    }
}
    `;
    }
}
CTypesPHPArray += ']';
let CTypesTCA = `<?php
$ctypes = ${CTypesPHPArray};
$defaultItems = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'];
$reducedItems = [];
foreach ($defaultItems as $key=>$item) {
    $duplicate = 0;
    foreach ($ctypes as $ctype) {
        if ($item[1] == $ctype[0]) {
            $duplicate = 1;
        }
    }
    if (!$duplicate) {
        array_push($reducedItems, $item);
    }
}
$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = $reducedItems;
foreach ($ctypes as $ctype) {
    \\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        ['LLL:EXT:theme/Resources/Private/Language/locallang_ctypes.xlf:'.$ctype[0].'_title',$ctype[0],$ctype[0]],
        'textmedia',
        'after'
    );
}`;
let CTypesIconRegistry = `<?php
$iconRegistry = \\TYPO3\\CMS\\Core\\Utility\\GeneralUtility::makeInstance(\\TYPO3\\CMS\\Core\\Imaging\\IconRegistry::class);
$extPath = \\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility::extPath('theme');
foreach (${CTypesPHPArray} as $ctype) {
    $filePath = '/Resources/Public/images/ctype-icons/default/'.$ctype[1].'.svg';
    if (is_file($extPath.$filePath)) {
        $iconRegistry->registerIcon(
            $ctype[0],
            \\TYPO3\\CMS\\Core\\Imaging\\IconProvider\\BitmapIconProvider::class,
            ['source' => $extPath.$filePath]
        );
    } else {
        $iconRegistry->registerIcon(
            $ctype[0],
            \\TYPO3\\CMS\\Core\\Imaging\\IconProvider\\BitmapIconProvider::class,
            ['source' => 'EXT:theme/Resources/Public/images/ctype-icons/default/Default.svg']
        );
    }
}`;
let CTypesIconClasses = `<?php
foreach (${CTypesPHPArray} as $ctype) {
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$ctype[0]] =  $ctype[0];
};`;
fs.writeFile(contentPath+"/_CTypes/CTypesTCA.php", CTypesTCA, function(){});
fs.writeFile(contentPath+"/_CTypes/CTypesIconRegistry.php", CTypesIconRegistry, function(){});
fs.writeFile(contentPath+"/_CTypes/CTypesIconClasses.php", CTypesIconClasses, function(){});
fs.writeFile(contentPath+"/_CTypes/CTypes.tsconfig", CTypesTSConfig, function(){});
fs.writeFile(contentPath+"/_CTypes/CTypes.typoscript", CTypesTS, function(){});
console.log(`CType configuration files created in ${contentPath}/_CTypes.`);


