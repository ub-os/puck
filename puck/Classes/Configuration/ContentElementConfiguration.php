<?php

namespace UBOS\Puck\Configuration;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use UBOS\Puck\Preview\BasicPreviewRenderer;
use UBOS\Puck\Controller;

/**
 * Provides a centralized way to define and configure TYPO3 content elements types (CTypes).
 *
 * Defines configuration for TCA, TypoScript setup, ext:container, Data Processors, FlexForms, etc.
 * Content elements defined here always render an Extbase plugin, default is "Content" @see Controller\ContentController
 * Flexform/data processing is only configured here, but must be executed in the controller, for example by using @see Controller\ContentModuleControllerTrait::prepareContentView()
 *
 * How to use:
 * Create a new instance for every CType and call its methods
 * 'addTCA' in @see puck/Configuration/TCA/Overrides/tt_content.php
 * 'addTypoScript' in @see puck/ext_localconf.php respectively.
 *
 * @see puck/Configuration/ContentElements/ for examples
 */
class ContentElementConfiguration
{
	/**
	 * @param string $type Content element CType (will be converted to lower_case_underscore and prefixed with the extension name)
	 * @param string $label Human-readable name of the content element
	 * @param string $description Description for the content element
	 * @param string $group Group in content element wizard (default: '01_content')
	 * @param string $icon Icon identifier (default: 'default')
	 * @param float $sorting Sorting value for content element wizard (default: 1000)
	 * @param string $showItem TCA showitem configuration
	 * @param array $columnsOverrides TCA column overrides
	 * @param string $pluginName Name of the plugin this element renders (default: 'Content')
	 * @param string $extensionName Plugin extension name (default: 'Puck')
	 * @param string $templateName Template name (defaults to CamelCase of $type). The default plugin 'Content' will look for a fluid component with this name in namespace UBOS\Puck\Modules\ which corresponds to folder puck/Resources/Private/FluidComponents/Modules/
	 * @param string $model Model class name, if content data should be mapped to a model instead of a generic record
	 * @param array $flexForms Array of field_name => EXT:ext/path/to/flexform.xml, e.g. ['pi_flexform' => 'EXT:puck/Configuration/FlexForms/ContentElement.xml']
	 * @param array $containerConfiguration Container column configuration
	 * @param array $dataProcessing Data processor configurations
	 * @param string $previewRenderer Class name for preview renderer (default: BasicPreviewRenderer::class)
	 * @param bool $noCache Whether to disable caching (default: false)
	 */
	public function __construct(
		public string $type,
		public string $label,
		public string $description,
		public string $group = '01_content',
		public string $icon = 'default',
		public float  $sorting = 1000,
		public string $showItem = '',
		public array  $columnsOverrides = [],
		public string $pluginName = 'Content',
		public string $extensionName = 'Puck',
		public string $templateName = '',
		public string $model = '',
		public array  $flexForms = [],
		public array  $containerConfiguration = [],
		public array  $dataProcessing = [],
		public string $previewRenderer = BasicPreviewRenderer::class,
		public bool   $noCache = false,
	)
	{
	}

	protected array $valueOverrides = [];

	/**
	 * Loads and sorts content element configurations from a specified folder
	 *
	 * @param string $folder Path to folder containing content element configurations
	 * @param string $extensionName Extension name (default: 'puck')
	 * @return array Sorted array of ContentElementConfiguration objects
	 */
	public static function getOrderedConfigurationsFromFolder(string $folder, string $extensionName = 'puck'): array
	{
		$configurations = [];
		$files = glob(ExtensionManagementUtility::extPath($extensionName, $folder));
		foreach ($files as $file) {
			$return = (include $file);
			if ($return instanceof ContentElementConfiguration) {
				$configurations[] = $return;
			}
		}
		usort($configurations, function ($a, $b) {
			return $a->sorting - $b->sorting;
		});
		return $configurations;
	}

	protected function getCType(): string
	{
		return GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName . '_' . $this->type);
	}

	/**
	 * Adds TCA configuration for the content element type
	 */
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
					->setBackendTemplate('')
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
				$GLOBALS['TCA']['tt_content']['columns'][$field]['config']['ds']['*' . ',' . $this->getCType()] = $flexForm;
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


	/**
	 * Adds the necessary TypoScript configuration for content element type
	 */
	public function addTypoScript(): void
	{
		if ($this->containerConfiguration && !isset($this->dataProcessing['container'])) {
			$this->dataProcessing['container'] = ['processor' => 'B13\Container\DataProcessing\ContainerProcessor', 'auto' => true];
		}
		if ($this->flexForms) {
			foreach ($this->flexForms as $fieldName => $flexForm) {
				if ($fieldName === 'pi_flexform') {
					continue;
				}
				if (!isset($this->dataProcessing[$fieldName . '-flex-form'])) {
					$this->dataProcessing[$fieldName . '-flex-form'] = ['processor' => 'flex-form', 'fieldName' => $fieldName, 'as' => $fieldName];
				}
			}
		}
		ExtensionManagementUtility::addTypoScript(
			$this->extensionName,
			'setup',
			'
            tt_content.' . $this->getCType() . ' = EXTBASEPLUGIN
            tt_content.' . $this->getCType() . '  {
                extensionName = ' . $this->extensionName . '
                pluginName = ' . $this->pluginName . '
                settings {
                	contentElementConfiguration {
						model = ' . $this->model . '
						templateName = ' . ($this->templateName ?: GeneralUtility::underscoredToUpperCamelCase($this->type)) . '
						dataProcessing {
							' . $this->getDataProcessingTypoScript($this->dataProcessing) . '
						}
                	}
                }
            }',
			'defaultContentRendering'
		);
	}

	/**
	 * Creates a new element configuration with restricted fields based on this configuration
	 *
	 * @param string $type Content element CType
	 * @param string $label Human-readable name for the content element
	 * @param string $description Description for the content element
	 * @param string $group Group in content element wizard (optional)
	 * @param string $icon Icon identifier (optional)
	 * @param float $sorting Sorting value for content element wizard (optional)
	 * @param array $valueOverrides These fields will be hidden in the backend and overridden with the given values when using ContentRecord
	 * @return ContentElementConfiguration The modified configuration instance
	 * @see \UBOS\Puck\Domain\ContentRecord::setOverriddenProperties
	 */
	public function makeRestrictedChildElement(
		string $type,
		string $label,
		string $description,
		string $group = '',
		string $icon = '',
		float  $sorting = 0,
		array  $valueOverrides = []
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
		$this->sorting = $sorting ?: $this->sorting;
		$this->setOverriddenFields($valueOverrides);
		return $this;
	}

	protected function setOverriddenFields(array $overrides): void
	{
		$this->valueOverrides = $overrides;
		foreach ($overrides as $fieldName => $value) {
			$this->columnsOverrides[$fieldName] = [
				'displayCond' => 'FIELD:CType:!=:' . $this->getCType(),
			];
		}
	}

	protected function getDataProcessingTypoScript(array $dataProcessing): string
	{
		$str = '';
		$i = 10;
		foreach ($dataProcessing as $index => $value) {
			$str .= $i . ' = ' . $value['processor'] . PHP_EOL;
			$str .= $i . '{' . PHP_EOL;
			foreach ($value as $key => $val) {
				if ($key == 'processor') {
					continue;
				}
				if ($key == 'dataProcessing') {
					$str .= '    ' . $key . ' { ' . PHP_EOL . $this->getDataProcessingTypoScript($val) . '    }' . PHP_EOL;
					continue;
				}
				$str .= '    ' . $key . ' = ' . $val . PHP_EOL;
			}
			$str .= '}' . PHP_EOL;
			$i += 10;
		}
		return $str;
	}
}