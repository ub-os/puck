<?php

namespace UBOS\Puck\Configuration;

use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Puck\Backend\BasicPreviewRenderer;
use UBOS\Puck\Controller;

/**
 * Provides a centralized way to define and configure TYPO3 content elements types (CTypes).
 *
 * Defines configuration for TCA, TypoScript setup, ext:container, Data Processors, FlexForms, etc.
 * Content elements can be rendered in two ways:
 * - Via FLUIDCOMPONENT (default) - directly renders a Fluid component
 * - Via EXTBASEPLUGIN (when $pluginName is set) - uses an Extbase controller for complex logic
 *
 * Data processing configuration is defined here and executed either by:
 * - FLUIDCOMPONENT content object (automatic)
 * - Extbase controller using @see Controller\ContentModuleControllerTrait::prepareContentView()
 *
 * How to use:
 * Create a new instance for every CType and call its methods:
 * - 'addTCA' in @see puck/Configuration/TCA/Overrides/tt_content.php
 * - 'addTypoScript' in @see puck/ext_localconf.php
 *
 * @see puck/Configuration/ContentElements/ for examples
 */
class ContentElementConfiguration
{
	protected array $valueOverrides = [];

	/**
	 * @var array<string, ContentElementConfiguration[]>
	 */
	private static array $folderConfigurationCache = [];
	/**
	 * @param string $type Content element CType (will be converted to lower_case_underscore and prefixed with the extension name)
	 * @param string $label Human-readable name of the content element
	 * @param string $description Description for the content element
	 * @param string $group Group in content element wizard (default: '01_content')
	 * @param string $icon Icon identifier (default: 'default')
	 * @param float $sorting Sorting value for content element wizard (default: 1000)
	 * @param string $showItem TCA showitem configuration
	 * @param array $columnsOverrides TCA column overrides
	 * @param string $pluginName Name of the Extbase plugin this element renders. If empty, FLUIDCOMPONENT is used instead of EXTBASEPLUGIN
	 * @param string $extensionName Extension name (default: 'Puck')
	 * @param string $componentCollection Component collection namespace (default: UBOS\Puck\Components\Modules)
	 * @param string $component Component name in dot notation (e.g., 'text' or 'stage.product'). If empty, defaults to camelCase version of $type
	 * @param array $flexForms Array of field_name => EXT:ext/path/to/flexform.xml, e.g. ['pi_flexform' => 'EXT:puck/Configuration/FlexForms/ContentElement.xml']
	 * @param array $containerConfiguration Container column configuration for ext:container
	 * @param array $dataProcessing Data processor configurations (processor class, options, etc.). 'record' processor is added by default. Container processor is added automatically if containerConfiguration is set.
	 * @param string $previewRenderer Class name for backend preview renderer (default: BasicPreviewRenderer::class)
	 */
	public function __construct(
		public string $type,
		public string $label,
		public string $description,
		public string $group = '01_content',
		public string $icon = 'default',
		public float $sorting = 1000,
		public string $showItem = '',
		public array $columnsOverrides = [],
		public string $pluginName = '',
		public string $extensionName = 'Puck',
		public string $componentCollection = 'UBOS\\Puck\\Components\\Modules',
		public string $component = '',
		public array $flexForms = [],
		public array $containerConfiguration = [],
		public array $dataProcessing = [],
		public string $previewRenderer = BasicPreviewRenderer::class,
	) {
		if (!isset($this->dataProcessing['record'])) {
			$this->dataProcessing['record'] = [
				'processor' => 'record-transformation',
			];
		}
		if ($this->containerConfiguration && !isset($this->dataProcessing['container'])) {
			$this->dataProcessing['container'] = [
				'processor' => 'B13\Container\DataProcessing\ContainerProcessor',
				'auto' => true,
			];
		}
	}

	/**
	 * Loads and sorts content element configurations from a specified folder.
	 *
	 * Memoized per (extension, folder) for the duration of the request: the same
	 * config objects are reused by the ext_localconf (addTypoScript) and TCA
	 * override (addTCA) passes instead of being globbed and re-included twice.
	 *
	 * @param string $folder Path to folder containing content element configurations
	 * @param string $extensionName Extension name (default: 'puck')
	 * @return ContentElementConfiguration[] Sorted array of ContentElementConfiguration objects
	 */
	public static function getOrderedConfigurationsFromFolder(string $folder, string $extensionName = 'puck'): array
	{
		$cacheKey = $extensionName . ':' . $folder;
		if (isset(self::$folderConfigurationCache[$cacheKey])) {
			return self::$folderConfigurationCache[$cacheKey];
		}
		$configurations = [];
		$files = glob(ExtensionManagementUtility::extPath($extensionName, $folder)) ?: [];
		foreach ($files as $file) {
			$return = (include $file);
			if ($return instanceof ContentElementConfiguration) {
				$configurations[] = $return;
			}
		}
		usort($configurations, static fn($a, $b): int => $a->sorting <=> $b->sorting);
		return self::$folderConfigurationCache[$cacheKey] = $configurations;
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
					$this->containerConfiguration
				))
					->setIcon($this->icon)
					->SetGroup($this->group)
					->setBackendTemplate('')
			);
		}
		$GLOBALS['TCA']['tt_content']['types'][$this->getCType()] = [
			'showitem' => $this->showItem,
			'columnsOverrides' => $this->columnsOverrides,
			'valueOverrides' => $this->valueOverrides,
		];
		foreach ($this->flexForms as $field => $flexForm) {
			if (isset($GLOBALS['TCA']['tt_content']['columns'][$field]['config']['ds'])) {
				$GLOBALS['TCA']['tt_content']['types'][$this->getCType()]['columnsOverrides'][$field]['config']['ds'] = $flexForm;
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
		);
	}

	/**
	 * Adds the necessary TypoScript configuration for content element type.
	 *
	 * Generates either:
	 * - FLUIDCOMPONENT rendering (default) - lightweight, direct component rendering
	 * - EXTBASEPLUGIN rendering (when $pluginName is set) - full Extbase controller
	 *
	 * For EXTBASEPLUGIN, contentElementConfiguration is stored in plugin settings
	 * to ensure it's available even when the plugin is rendered outside of tt_content context
	 * (e.g., via USER object or custom page type).
	 */
	public function addTypoScript(): void
	{
		if ($this->containerConfiguration && !isset($this->dataProcessing['container'])) {
			$this->dataProcessing['container'] = ['processor' => 'B13\Container\DataProcessing\ContainerProcessor', 'auto' => true];
		}

		$component = $this->component ?: GeneralUtility::underscoredToLowerCamelCase($this->type);

		if (!empty($this->pluginName)) {
			// Use EXTBASEPLUGIN for elements that need controller logic
			$content = '
            tt_content.' . $this->getCType() . ' = EXTBASEPLUGIN
            tt_content.' . $this->getCType() . '  {
                extensionName = ' . $this->extensionName . '
                pluginName = ' . $this->pluginName . '
            }
            plugin.tx_' . strtolower($this->extensionName) . '_' . strtolower($this->pluginName) . '.settings.contentElementConfiguration {
				componentCollection = ' . $this->componentCollection . '
				component = ' . $component . '
				dataProcessing {
					' . $this->getDataProcessingTypoScript($this->dataProcessing) . '
				}
			}
            ';
		} else {
			// Use FLUIDCOMPONENT for simple content elements (default)
			$content = '
            tt_content.' . $this->getCType() . ' = FLUIDCOMPONENT
            tt_content.' . $this->getCType() . ' {
                componentCollection = ' . $this->componentCollection . '
                component = ' . $component . '
                dataProcessing {
                    ' . $this->getDataProcessingTypoScript($this->dataProcessing) . '
                }
            }';
		}
		ExtensionManagementUtility::addTypoScript(
			$this->extensionName,
			'setup',
			$content,
			'defaultContentRendering'
		);
	}

	/**
	 * Reconfigures this instance in place as a restricted variant of another content
	 * element, reusing its component template but exposing fewer fields to editors.
	 *
	 * Mutates and returns $this — safe because every content element config file is
	 * `include`d into a fresh instance (see getOrderedConfigurationsFromFolder()).
	 *
	 * This is useful for creating preset variants of a content element that offer
	 * fewer configuration options to editors, making them simpler to use while
	 * sharing the same component template.
	 *
	 * @param string $type Content element CType
	 * @param string $label Human-readable name for the content element
	 * @param string $description Description for the content element
	 * @param string $group Group in content element wizard (optional)
	 * @param string $icon Icon identifier (optional)
	 * @param float $sorting Sorting value for content element wizard (optional)
	 * @param array $valueOverrides Fields that will be hidden in the backend and overridden with the given values
	 * @return ContentElementConfiguration This instance, reconfigured
	 * @see \UBOS\Puck\Record\ContentRecord::setOverriddenProperties
	 */
	public function makeRestrictedChildElement(
		string $type,
		string $label,
		string $description,
		string $group = '',
		string $icon = '',
		float $sorting = 0,
		array $valueOverrides = []
	): ContentElementConfiguration {
		if (!$this->component) {
			$this->component = GeneralUtility::underscoredToLowerCamelCase($this->type);
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

	/**
	 * Sets fields that should be overridden with fixed values and hidden from editors
	 */
	protected function setOverriddenFields(array $overrides): void
	{
		$this->valueOverrides = $overrides;
		foreach ($overrides as $fieldName => $value) {
			$this->columnsOverrides[$fieldName] = [
				'displayCond' => 'FIELD:CType:!=:' . $this->getCType(),
			];
		}
	}

	/**
	 * Converts data processing configuration array to TypoScript string
	 */
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
