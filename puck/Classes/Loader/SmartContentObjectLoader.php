<?php

namespace UBOS\Puck\Loader;

use ReflectionClass;
use ReflectionException;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use UBOS\Puck\Attribute\ContainerElement;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Attribute\FlexFormProperty;
use UBOS\Puck\Attribute\PluginElement;
use UBOS\Puck\Preview\PuckPreviewRenderer;
use UBOS\Puck\Utility\PuckUtility;

/**
 * Class SmartContentObjectLoader <br>
 * Loads all classes from the puck/Classes/Domain/Model/Content folder <br>
 * Registers them as content elements based on class attributes ContentElementWizard, ContainerElement and PluginElement <br>
 * Registering them as content elements is done by adding them to the CType select, New Content Element Wizard, defining the frontend typoscript
 */
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
                'typeKey' => self::EXTENSION_KEY . '_' . GeneralUtility::camelCaseToLowerCaseUnderscored($model),
                'lowerCaseUnderscored' => GeneralUtility::camelCaseToLowerCaseUnderscored($model),
            ];
        }
        return $index;
    }

    /**
     * @throws ReflectionException
     */
    public static function sortModelsByWizardTabAndOrder(array $models): array
    {
        $wizardGroups = [];
        foreach($models as $model) {
            $tab = '01_content';
            $order = 10;
            $refClass = new ReflectionClass($model['fullName']);
            $refContentElementWizard = $refClass->getAttributes(ContentElementWizard::class)[0] ?? null;
            if ($refContentElementWizard) {
                $contentElementWizard = $refContentElementWizard->newInstance();
                $tab = $contentElementWizard->tab ?? $tab;
                $order = $contentElementWizard->order ?? $order;
            }
            if (!isset($wizardGroups[$tab])) {
                $wizardGroups[$tab] = [];
            }
            if (!isset($wizardGroups[$tab][$order])) {
                $wizardGroups[$tab][$order] = $model;
            } else {
                $wizardGroups[$tab][] = $model;
            }
        }
        ksort($wizardGroups);
        foreach($wizardGroups as $groupKey => $group) {
            ksort($wizardGroups[$groupKey]);
        }
        return $wizardGroups;
    }

    /**
     * @throws ReflectionException
     */
    public static function registerTypes(): void
    {
        $modelIndex = static::indexModels();
        $wizardGroups = static::sortModelsByWizardTabAndOrder($modelIndex);
        foreach($wizardGroups as $groupKey => $group) {
            ExtensionManagementUtility::addTcaSelectItemGroup(
                'tt_content',
                'CType',
                $groupKey,
                'LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:wizard.'.$groupKey.'.header',
            );
            foreach($group as $model) {
                $refClass = new ReflectionClass($model['fullName']);
                //$refPluginElement = $refClass->getAttributes(PluginElement::class)[0] ?? null;
                $refContainerElement = $refClass->getAttributes(ContainerElement::class)[0] ?? null;

//                if ($refPluginElement) {
//                    $piFlexFormValue = $refPluginElement->newInstance()->piFlexFormValue;
//                    if ($piFlexFormValue) {
//                        ExtensionManagementUtility::addPiFlexFormValue(
//                            '*',
//                            $piFlexFormValue,
//                            $model['typeKey']
//                        );
//                    }
//                }

                foreach ($refClass->getProperties() as $property) {
                    $refFlexFormProperty = $property->getAttributes(FlexFormProperty::class) ?? null;
                    if ($refFlexFormProperty) {
                        $column = GeneralUtility::camelCaseToLowerCaseUnderscored($property->getName());
                        $flexFormValue = $refFlexFormProperty[0]->newInstance()->flexFormValue;
                        if ($flexFormValue) {
                            if (is_array($GLOBALS['TCA']['tt_content']['columns']) && is_array($GLOBALS['TCA']['tt_content']['columns'][$column]['config']['ds'])) {
                                $GLOBALS['TCA']['tt_content']['columns'][$column]['config']['ds']['*' . ',' . $model['typeKey']] = $flexFormValue;
                            }
                        }
                    }
                }

                if ($refContainerElement) {
                    $containerConfiguration = $refContainerElement->newInstance()->configuration;
                    if ($containerConfiguration) {
                        GeneralUtility::makeInstance(Registry::class)->configureContainer(
                            (
                            new ContainerConfiguration(
                                $model['typeKey'], // CType
                                'LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:content.element.' . GeneralUtility::camelCaseToLowerCaseUnderscored($model['name']),
                                'LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:wizard.' . GeneralUtility::camelCaseToLowerCaseUnderscored($model['name']).'.description', // description
                                $containerConfiguration // configuration
                            )
                            )
                                ->setIcon('EXT:puck/Resources/Public/Icons/Backend/'.$model['name'].'.svg')
                                ->SetGroup($groupKey)
                                ->setRegisterInNewContentElementWizard(false)
                        );
                        $GLOBALS['TCA']['tt_content']['types'][$model['typeKey']]['previewRenderer'] = PuckPreviewRenderer::class;
                        continue;
                    }
                }

                $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$model['typeKey']] = $model['lowerCaseUnderscored'];
                ExtensionManagementUtility::addTcaSelectItem(
                    'tt_content',
                    'CType',
                    [
                        'LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:content.element.'.$model['lowerCaseUnderscored'],
                        $model['typeKey'],
                        $model['lowerCaseUnderscored'],
                        $groupKey
                    ],
                );
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function addTypesTSconfig(): void
    {
        $modelIndex = static::indexModels();
        $wizardGroups = static::sortModelsByWizardTabAndOrder($modelIndex);
        foreach($wizardGroups as $groupKey => $group) {
            ExtensionManagementUtility::addPageTSConfig('
                mod.wizards.newContentElement.wizardItems.'.$groupKey.' {
                  header = LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:wizard.'.$groupKey.'.header
                }
            ');
            foreach($group as $model) {
                ExtensionManagementUtility::addPageTSConfig('
                mod.wizards.newContentElement.wizardItems.'.$groupKey.'.elements.'.$model['typeKey'].' {
                        iconIdentifier = '.$model['lowerCaseUnderscored'].'
                        title = LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:wizard.'.$model['lowerCaseUnderscored'].'
                        description = LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:wizard.'.$model['lowerCaseUnderscored'].'.description
                        tt_content_defValues {
                            CType = '.$model['typeKey'].'
                        }
                }
                mod.wizards.newContentElement.wizardItems.'.$groupKey.'.show := addToList('.$model['typeKey'].')
            ');
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function addTypesTypoScript(): void
    {
        $modelIndex = static::indexModels();
        foreach($modelIndex as $model) {
            $pluginName = 'Content';
            $containerDataProcessing = '';

            $refClass = new ReflectionClass($model['fullName']);
            $refPluginElement = $refClass->getAttributes(PluginElement::class)[0] ?? null;
            $refContainerElement = $refClass->getAttributes(ContainerElement::class)[0] ?? null;

            if ($refPluginElement) {
                $pluginName = $refPluginElement->newInstance()->pluginName ?? $pluginName;
            }

            if ($refContainerElement) {
                $containerConfiguration = $refContainerElement->newInstance()->configuration;
                if ($containerConfiguration && is_array($containerConfiguration)) {
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
                pluginName = ' . $pluginName . '
                vendorName = UBOS
                settings {
                    contentElement = '.$model['name'].'
                    extensionKey = puck
                    vendorName = UBOS
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