<?php

namespace UBOS\Puck\Loader;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use UBOS\Puck\Attribute\ContainerElement;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Attribute\PluginElement;
use UBOS\Puck\Preview\PuckPreviewRenderer;
use UBOS\Puck\Utility\PuckUtility;

/**
 * Class SmartContentObjectLoader <br>
 * Loads all classes from the puck/Classes/Domain/Model/Content folder
 * and registers them as content elements based on class attributes ContainerElement, ContentElementWizard and PluginElement
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

    public static function sortModelsByWizardTabAndOrder(array $models): array
    {
        $wizardGroups = [];
        foreach($models as $model) {
            $tab = '01_content';
            $order = 10;
            $reflectionClass = new \ReflectionClass($model['fullName']);
            $wizardAttribute = $reflectionClass->getAttributes(ContentElementWizard::class)[0] ?? null;
            if ($wizardAttribute) {
                $wizardAttributeInstance = $wizardAttribute->newInstance();
                $tab = $wizardAttributeInstance->tab ?? $tab;
                $order = $wizardAttributeInstance->order ?? $order;
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

    public static function registerTypes(): void
    {
        $modelIndex = static::indexModels();
        $wizardGroups = static::sortModelsByWizardTabAndOrder($modelIndex);
        foreach($wizardGroups as $groupKey => $group) {
            ExtensionManagementUtility::addTcaSelectItemGroup(
                'tt_content',
                'CType',
                $groupKey,
                'LLL:EXT:puck/Resources/Private/Language/locallang.xlf:wizard.'.$groupKey.'.header',
            );
            foreach($group as $model) {
                $reflectionClass = new \ReflectionClass($model['fullName']);
                $pluginElementAttribute = $reflectionClass->getAttributes(PluginElement::class)[0] ?? null;
                $containerElementAttribute = $reflectionClass->getAttributes(ContainerElement::class)[0] ?? null;

                if ($pluginElementAttribute) {
                    $piFlexFormValue = $pluginElementAttribute->newInstance()->piFlexFormValue;
                    if ($piFlexFormValue) {
                        ExtensionManagementUtility::addPiFlexFormValue(
                            '*',
                            $piFlexFormValue,
                            $model['typeKey']
                        );
                    }
                }

                if ($containerElementAttribute) {
                    $containerConfiguration = $containerElementAttribute->newInstance()->configuration;
                    if ($containerConfiguration) {
                        GeneralUtility::makeInstance(Registry::class)->configureContainer(
                            (
                            new ContainerConfiguration(
                                $model['typeKey'], // CType
                                'LLL:EXT:puck/Resources/Private/Language/locallang.xlf:content.element.' . GeneralUtility::camelCaseToLowerCaseUnderscored($model['name']),
                                'LLL:EXT:puck/Resources/Private/Language/locallang.xlf:wizard.' . GeneralUtility::camelCaseToLowerCaseUnderscored($model['name']).'.description', // description
                                $containerConfiguration // configuration
                            )
                            )
                                ->setIcon('EXT:puck/Resources/Public/Icons/Content/'.$model['name'].'.svg')
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
                        'LLL:EXT:puck/Resources/Private/Language/locallang.xlf:content.element.'.$model['lowerCaseUnderscored'],
                        $model['typeKey'],
                        $model['lowerCaseUnderscored'],
                        $groupKey
                    ],
                );
            }
        }
    }

    public static function addTypesTSconfig(): void
    {
        $modelIndex = static::indexModels();
        $wizardGroups = static::sortModelsByWizardTabAndOrder($modelIndex);
        foreach($wizardGroups as $groupKey => $group) {
            ExtensionManagementUtility::addPageTSConfig('
                mod.wizards.newContentElement.wizardItems.'.$groupKey.' {
                  header = LLL:EXT:puck/Resources/Private/Language/locallang.xlf:wizard.'.$groupKey.'.header
                }
            ');
            foreach($group as $model) {
                ExtensionManagementUtility::addPageTSConfig('
                mod.wizards.newContentElement.wizardItems.'.$groupKey.'.elements.'.$model['typeKey'].' {
                        iconIdentifier = '.$model['lowerCaseUnderscored'].'
                        title = LLL:EXT:puck/Resources/Private/Language/locallang.xlf:wizard.'.$model['lowerCaseUnderscored'].'
                        description = LLL:EXT:puck/Resources/Private/Language/locallang.xlf:wizard.'.$model['lowerCaseUnderscored'].'.description
                        tt_content_defValues {
                            CType = '.$model['typeKey'].'
                        }
                }
                mod.wizards.newContentElement.wizardItems.'.$groupKey.'.show := addToList('.$model['typeKey'].')
            ');
            }
        }
    }

    public static function addTypesTypoScript(): void
    {
        $modelIndex = static::indexModels();
        foreach($modelIndex as $model) {
            $pluginName = 'Content';
            $containerDataProcessing = '';

            $reflectionClass = new \ReflectionClass($model['fullName']);
            $pluginElementAttribute = $reflectionClass->getAttributes(PluginElement::class)[0] ?? null;
            $containerElementAttribute = $reflectionClass->getAttributes(ContainerElement::class)[0] ?? null;

            if ($pluginElementAttribute) {
                $pluginName = $pluginElementAttribute->newInstance()->pluginName ?? $pluginName;
            }

            if ($containerElementAttribute) {
                $containerConfiguration = $containerElementAttribute->newInstance()->configuration;
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