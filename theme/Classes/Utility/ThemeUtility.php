<?php
namespace UBOS\Theme\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use HDNET\Autoloader\Utility\FileUtility;

class ThemeUtility
{
    public static function indexContentModels(): array
    {
        $extensionKey = 'theme';
        $modelPath = ExtensionManagementUtility::extPath($extensionKey) . 'Classes/Domain/Model/Content/';
        $models = FileUtility::getBaseFilesInDir($modelPath, 'php');
        $index = [];
        foreach($models as $model) {
            $index[] = [
                'name' => $model,
                'fullName' => 'UBOS\\Theme\\Domain\\Model\\Content\\'.$model,
                'typeKey' => $extensionKey.'_'.GeneralUtility::camelCaseToLowerCaseUnderscored($model)
            ];
        }
        return $index;
    }
}