<?php
namespace UBOS\Puck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use HDNET\Autoloader\Utility\FileUtility;

class PuckUtility
{
    public static function indexContentModels(): array
    {
        $extensionKey = 'puck';
        $modelPath = ExtensionManagementUtility::extPath($extensionKey) . 'Classes/Domain/Model/Content/';
        $models = FileUtility::getBaseFilesInDir($modelPath, 'php');
        $index = [];
        foreach($models as $model) {
            $index[] = [
                'name' => $model,
                'fullName' => 'UBOS\\Puck\\Domain\\Model\\Content\\'.$model,
                'typeKey' => $extensionKey.'_'.GeneralUtility::camelCaseToLowerCaseUnderscored($model)
            ];
        }
        return $index;
    }
}