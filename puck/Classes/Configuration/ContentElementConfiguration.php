<?php

namespace UBOS\Puck\Configuration;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use UBOS\Puck\Preview\BasicPreviewRenderer;

class ContentElementConfiguration
{
    public function __construct(
        public string $type,
        public string $label,
        public string $description,
        public string $group = '01_content',
        public string $icon = 'default',
        public string $showItem = '',
        public array $columnsOverrides = [],
        public string $pluginName = 'Content',
        public string $extensionName = 'Puck',
        public string $vendorName = 'UBOS',
        public string $templateName = '',
        public string $model = '',
        public array $flexForms = [],
        public array $containerConfiguration = [],
        public array $dataProcessing = [],
        public string $previewRenderer = BasicPreviewRenderer::class,
        public bool $noCache = false,
    )
    {
    }

    protected array $valueOverrides = [];

    public function getCType(): string
    {
        return GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName. '_'. $this->type);
    }

    public function addTCA(): void
    {
        if ($this->containerConfiguration) {
            GeneralUtility::makeInstance(Registry::class)->configureContainer(
                (new ContainerConfiguration(
                    $this->getCType(),
                    $this->label,
                    $this->description,
                    $this->containerConfiguration))
                    ->setIcon($this->icon)->SetGroup($this->group)
                    ->setRegisterInNewContentElementWizard(false)
            );
        }
        $GLOBALS['TCA']['tt_content']['types'][$this->getCType()] = [
            'showitem' => $this->showItem,
            'columnsOverrides' => $this->columnsOverrides,
            'valueOverrides' => $this->valueOverrides,
        ];
        foreach ($this->flexForms as $field => $flexForm) {
            if (is_array($GLOBALS['TCA']['tt_content']['columns'][$field]['config']['ds'])) {
                $GLOBALS['TCA']['tt_content']['columns'][$field]['config']['ds']['*'. ','. $this->getCType()] = $flexForm;
            }
        }
        if ($this->previewRenderer) {
            $GLOBALS['TCA']['tt_content']['types'][$this->getCType()]['previewRenderer'] = $this->previewRenderer;
        }
        ExtensionManagementUtility::addPlugin(
            [
                'label' => $this->label,
                'description' => $this->description,
                'group' => $this->group,
                'value' => $this->getCType(),
                'icon' => $this->icon,
            ],
            'CType',
            'puck',
        );
    }

    public function addTypoScript(): void
    {
        if ($this->containerConfiguration && !isset($this->dataProcessing['container'])) {
            $this->dataProcessing['container'] = ['processor' => 'B13\Container\DataProcessing\ContainerProcessor', 'auto' => true];
        }
        if ($this->flexForms) {
            foreach($this->flexForms as $fieldName => $flexForm) {
                if (!isset($this->dataProcessing[$fieldName. '-flex-form'])) {
                    $this->dataProcessing[$fieldName. '-flex-form'] = ['processor' => 'flex-form', 'fieldName' => $fieldName, 'as' => $fieldName];
                }
            }
        }
        ExtensionManagementUtility::addTypoScript(
            $this->extensionName,
            'setup',
            '
            tt_content.'.$this->getCType().' = USER'. ($this->noCache ? '_INT' : ''). '
            tt_content.'.$this->getCType().'  {
                userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
                extensionName = ' . $this->extensionName . '
                pluginName = ' . $this->pluginName . '
                vendorName = ' . $this->vendorName . '
                settings {
                    pluginName = ' . $this->pluginName . '
                    extensionKey = ' . $this->extensionName . '
                    vendorName = ' . $this->vendorName . '
                    templateName = ' . ($this->templateName ?: GeneralUtility::underscoredToUpperCamelCase($this->type)) . '
                    model = ' . $this->model . '
                    dataProcessing {
                        ' . $this->getDataProcessingTypoScript($this->dataProcessing) . '
                    }
                }
            }',
            'defaultContentRendering'
        );
    }

    public function makeRestrictedChildElement(
        string $type,
        string $label,
        string $description,
        string $group = '',
        string $icon = '',
        array $valueOverrides = []
    ): ContentElementConfiguration
    {
        if (!$this->templateName) {
            $this->templateName = GeneralUtility::underscoredToUpperCamelCase($this->type);
        }
        $this->type = $type;
        $this->label = $label;
        $this->description = $description;
        $this->group = $group ?: $this->group;
        $this->icon = $icon ?: $this->icon;
        $this->setOverriddenFields($valueOverrides);
        return $this;
    }

    protected function setOverriddenFields(array $overrides): void
    {
        $this->valueOverrides = $overrides;
        foreach ($overrides as $fieldName => $value) {
            $this->columnsOverrides[$fieldName] = [
                'displayCond' => 'FIELD:CType:!=:'. $this->getCType(),
/*                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [['label' => '', 'value' => $value]],
                    'default' => $value,
                ]*/
            ];
        }
    }

    protected function getDataProcessingTypoScript(array $dataProcessing): string
    {
        $str = '';
        $i = 10;
        foreach ($dataProcessing as $index => $value) {
            $str .= $i. ' = '. $value['processor']. PHP_EOL;
            $str .= $i. '{'. PHP_EOL;
            foreach ($value as $key => $val) {
                if ($key == 'processor') {
                    continue;
                }
                if ($key == 'dataProcessing') {
                    $str .= '    '. $key. ' { '. PHP_EOL. $this->getDataProcessingTypoScript($val). '    }'. PHP_EOL;
                    continue;
                }
                $str .= '    '. $key. ' = '. $val. PHP_EOL;
            }
            $str .= '}'. PHP_EOL;
            $i += 10;
        }
        return $str;
    }
}