<?php

namespace UBOS\Puck\Loader;

use Doctrine\Common\Annotations\AnnotationReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use B13\Container\Tca\Registry;
use B13\Container\Tca\ContainerConfiguration;
use UBOS\Puck\Utility\PuckUtility;

use UBOS\Puck\Preview\PuckPreviewRenderer;

class SmartContainerContentObjectLoader extends SmartContentObjectLoader
{
    public const FOLDER = 'Classes/Domain/Model/Content/Container/';
    public const NAMESPACE = 'UBOS\\Puck\\Domain\\Model\\Content\\Container\\';
    public const DEFAULT_CONTAINER_CONFIGURATION = [
        [
            ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_column']]
        ],
    ] ;

    public static function registerTypes(): void
    {
        $modelIndex = static::indexModels();
        $reader = new AnnotationReader();
        foreach($modelIndex as $model) {
            $reflectionClass = new \ReflectionClass($model['fullName']);
            $wizardTabAnnotation = $reader->getClassAnnotation($reflectionClass, 'HDNET\Autoloader\Annotation\WizardTab');
            //$containerAnnotation = $reader->getClassAnnotation($reflectionClass, 'UBOS\\Puck\\Annotation\\ContentContainer');
            $containerConfiguration = $model['fullName']::CONTAINER_CONFIGURATION ?? static::DEFAULT_CONTAINER_CONFIGURATION;
            GeneralUtility::makeInstance(Registry::class)->configureContainer(
                (
                new ContainerConfiguration(
                    $model['typeKey'], // CType
                    'LLL:EXT:puck/Resources/Private/Language/locallang.xlf:content.element.' . GeneralUtility::camelCaseToLowerCaseUnderscored($model['name']).'',
                    'LLL:EXT:puck/Resources/Private/Language/locallang.xlf:wizard.' . GeneralUtility::camelCaseToLowerCaseUnderscored($model['name']).'.description', // description
                    $containerConfiguration // configuration
                )
                )
                    ->setIcon('EXT:puck/Resources/Public/Icons/Content/'.$model['name'].'.svg')
                    ->SetGroup($wizardTabAnnotation)
            );
            $GLOBALS['TCA']['tt_content']['types'][$model['typeKey']]['previewRenderer'] = PuckPreviewRenderer::class;

        }
    }

    public static function addTypesTypoScript(): void
    {
        $modelIndex = static::indexModels();
        foreach($modelIndex as $model) {
            $containerConfiguration = $model['fullName']::CONTAINER_CONFIGURATION ?? static::DEFAULT_CONTAINER_CONFIGURATION;
            $containerDataProcessing = '';
            foreach($containerConfiguration as $row) {
                foreach($row as $column) {
                    $containerDataProcessing .= '
            '.$column['colPos'].' = B13\Container\DataProcessing\ContainerProcessor
            '.$column['colPos'].' {
                colPos = '.$column['colPos'].'
                as = children_'.$column['colPos'].'
            }
            ';
                }
            }
            ExtensionManagementUtility::addTypoScript(
                'puck',
                'setup',
                '
        tt_content.'.$model['typeKey'].' = COA
        tt_content.'.$model['typeKey'].'.20 = USER
        tt_content.'.$model['typeKey'].'.20  {
                userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
                extensionName = Puck
                pluginName = Content
                vendorName = UBOS
                settings {
                    contentElement = '.$model['name'].'
                    extensionKey = puck
                    vendorName = UBOS
                    classPath = Domain/Model/Content/Container/
                    dataProcessing {
                        '.$containerDataProcessing.'
                    }
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