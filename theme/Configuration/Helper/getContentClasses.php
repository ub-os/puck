<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$classes = [];
foreach(glob(ExtensionManagementUtility::extPath('theme').'Classes/Domain/Model/Content/*.php') as $contentFile) {
    $name = str_replace('.php', '', basename($contentFile));
    $classes[] = [
        'name' => $name,
        'fullName' => 'UBOS\\Theme\\Domain\\Model\\Content\\'.$name,
        'ctype' => 'theme_'.ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $name)), '_')
    ];
}
return $classes;