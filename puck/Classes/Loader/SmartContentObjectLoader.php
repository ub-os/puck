<?php

namespace UBOS\Puck\Loader;

use Doctrine\Common\Annotations\AnnotationReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Utility\PuckUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

// todo: atm only overrides typoscript, registration is done by ext:Autoloader, to be replaced
class SmartContentObjectLoader
{
    public const EXTENSION_KEY = 'puck';
    public const FOLDER = 'Classes/Domain/Model/Content/';
    public const NAMESPACE = 'UBOS\\Puck\\Domain\\Model\\Content\\';

    public static function indexModels(): array
    {
        $modelPath = ExtensionManagementUtility::extPath(self::EXTENSION_KEY) . static::FOLDER;
        $models = PuckUtility::getBaseFilesInDir($modelPath, 'php');
        $index = [];
        foreach($models as $model) {
            $index[] = [
                'name' => $model,
                'fullName' => static::NAMESPACE . $model,
                'typeKey' => self::EXTENSION_KEY . '_' . GeneralUtility::camelCaseToLowerCaseUnderscored($model)
            ];
        }
        return $index;
    }

    public static function registerTypes(): void
    {
        $modelIndex = static::indexModels();
        $reader = new AnnotationReader();
        foreach($modelIndex as $model) {
            $reflectionClass = new \ReflectionClass($model['fullName']);
            $piFlexFormValue = $reader->getClassAnnotation($reflectionClass, 'UBOS\Puck\Annotation\PluginElement')->piFlexFormValue ?? '';
            if ($piFlexFormValue) {
                ExtensionManagementUtility::addPiFlexFormValue(
                    '*',
                    $piFlexFormValue,
                    $model['typeKey']
                );
            }
        }
    }

    public static function addTypesTypoScript(): void
    {
        $modelIndex = static::indexModels();
        $reader = new AnnotationReader();
        foreach($modelIndex as $model) {
            $reflectionClass = new \ReflectionClass($model['fullName']);
            $pluginName = $reader->getClassAnnotation($reflectionClass, 'UBOS\Puck\Annotation\PluginElement')->pluginName ?? 'Content';
            ExtensionManagementUtility::addTypoScript(
                'puck',
                'setup',
                '
        tt_content.'.$model['typeKey'].' = COA
        tt_content.'.$model['typeKey'].'.20 = USER
        tt_content.'.$model['typeKey'].'.20  {
                userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
                extensionName = Puck
                pluginName = ' . $pluginName . '
                vendorName = UBOS
                settings {
                    contentElement = '.$model['name'].'
                    extensionKey = puck
                    vendorName = UBOS
                    view {
                        templateRootPath = EXT:puck/Resources/Private/Fluid/
                    }
                }   
        }',
                'defaultContentRendering'
            );
        }
    }

}