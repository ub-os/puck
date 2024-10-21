<?php

namespace UBOS\Puck;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;

class ContentElementDefinition
{
    public function __construct(
        protected string $key,
        protected string $label,
        protected string $description,
        protected string $group = '01_content',
        protected string $icon = 'default',
        protected string $showItem = '',
        protected array $columnsOverrides = [],
        protected string $pluginName = 'Content',
        protected string $extensionName = 'Puck',
        protected string $vendorName = 'UBOS',
        protected string $model = '',
        protected array $flexForms = [],
        protected array $containerConfiguration = [],
        protected array $dataProcessing = [],
        protected string $previewRenderer = '',
        protected bool $noCache = false
    )
    {
        $this->CType = GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName. '_'. $this->key);
    }

    public function addTCA(): void
    {
        {
            if (str_starts_with($this->icon, 'EXT:')) {
            }
            $GLOBALS['TCA']['tt_content']['types'][$this->CType] = [
                'showitem' => $this->showItem,
                'columnsOverrides' => $this->columnsOverrides
            ];
            foreach ($this->flexForms as $field => $flexForm) {
                if (is_array($GLOBALS['TCA']['tt_content']['columns'][$field]['config']['ds'])) {
                    $GLOBALS['TCA']['tt_content']['columns'][$field]['config']['ds']['*'. ','. $this->CType] = $flexForm;
                }
            }
            if ($this->previewRenderer) {
                $GLOBALS['TCA']['tt_content']['types'][$this->CType]['previewRenderer'] = $this->previewRenderer;
            }
            if ($this->containerConfiguration) {
                GeneralUtility::makeInstance(Registry::class)->configureContainer(
                    (new ContainerConfiguration(
                        $this->CType,
                        $this->label,
                        $this->description,
                        $this->containerConfiguration))
                        ->setIcon($this->icon)
                        ->SetGroup($this->group)
                        ->setRegisterInNewContentElementWizard(false)
                );
            }
            ExtensionManagementUtility::addPlugin(
                [
                    'label' => $this->label,
                    'description' => $this->description,
                    'group' => $this->group,
                    'value' => $this->CType,
                    'icon' => $this->icon,
                ],
                'CType',
                'puck',
            );
        }
    }

    public function addTypoScript(): void
    {
        ExtensionManagementUtility::addTypoScript(
            $this->extensionName,
            'setup',
            '
            tt_content.'.$this->CType.' = USER'. ($this->noCache ? '_INT' : ''). '
            tt_content.'.$this->CType.'  {
                userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
                extensionName = ' . $this->extensionName . '
                pluginName = ' . $this->pluginName . '
                vendorName = ' . $this->vendorName . '
                settings {
                    pluginName = ' . $this->pluginName . '
                    extensionKey = ' . $this->extensionName . '
                    vendorName = ' . $this->vendorName . '
                    templateName = ' . GeneralUtility::underscoredToUpperCamelCase($this->key) . '
                    model = ' . $this->model . '
                    dataProcessing {
                        ' . $this->getDataProcessingString($this->dataProcessing) . '
                    }
                }
            }',
            'defaultContentRendering'
        );
    }

    protected function getDataProcessingString(array $dataProcessing): string
    {
        $str = '';
        foreach ($dataProcessing as $index => $value) {
            $str .= $index. ' = '. $value['processor']. PHP_EOL;
            $str .= $index. '{'. PHP_EOL;
            foreach ($value as $key => $val) {
                if ($key == 'processor') {
                    continue;
                }
                if ($key == 'dataProcessing') {
                    $str .= '    '. $key. ' { '. PHP_EOL. $this->getDataProcessingString($val). '    }'. PHP_EOL;
                    continue;
                }
                $str .= '    '. $key. ' = '. $val. PHP_EOL;
            }
            $str .= '}'. PHP_EOL;
        }
        return $str;
    }
}