<?php

declare(strict_types=1);

namespace UBOS\Puck\ContentObject;

use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Contains FLUIDCOMPONENT class object for rendering Fluid components.
 *
 * Similar to FLUIDTEMPLATE but directly renders Fluid components using ComponentRenderer.
 *
 * Example TypoScript:
 * 10 = FLUIDCOMPONENT
 * 10 {
 *   componentCollection = MyVendor\MyExt\Components\MyComponentCollection
 *   component = my.component
 *   dataProcessing {
 *     10 = TYPO3\CMS\Frontend\DataProcessing\FilesProcessor
 *     10.references.fieldName = media
 *   }
 *   variables {
 *     mylabel = TEXT
 *     mylabel.value = Label from TypoScript
 *   }
 * }
 */
class FluidComponentContentObject extends AbstractContentObject
{
	public function __construct(
		private readonly RenderingContextFactory $renderingContextFactory,
		private readonly RecordFactory           $recordFactory,
		private readonly ContentDataProcessor    $contentDataProcessor,
	)
	{
	}

	/**
	 * Renders the Fluid component
	 *
	 * @param array $conf TypoScript configuration
	 * @return string Rendered component content
	 */
	public function render($conf = []): string
	{
		$componentName = $this->resolveComponentName($conf);
		if ($componentName === '') {
			return '';
		}

		$componentCollection = $this->getComponentCollection($conf);
		if ($componentCollection === null) {
			return '';
		}

		$variables = $this->getContentObjectVariables($conf);
		$renderingContext = $this->createRenderingContext();

		try {
			$componentRenderer = $componentCollection->getComponentRenderer();
			return $componentRenderer->renderComponent(
				$componentName,
				$variables,
				[],
				$renderingContext
			);
		} catch (\Exception $e) {
			if ($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false) {
				return sprintf(
					'<!-- FLUIDCOMPONENT ERROR: %s -->',
					htmlspecialchars($e->getMessage())
				);
			}
			return '';
		}
	}

	/**
	 * Resolves the component name from configuration
	 *
	 * @param array $conf TypoScript configuration
	 * @return string Component name
	 */
	protected function resolveComponentName(array $conf): string
	{
		$component = $conf['component'] ?? '';

		if (empty($component)) {
			// Fallback to viewHelperName for backwards compatibility
			$component = $conf['viewHelperName'] ?? '';
		}

		return trim($component);
	}

	/**
	 * Gets or creates the component collection instance
	 *
	 * @param array $conf TypoScript configuration
	 * @return AbstractComponentCollection|null
	 */
	protected function getComponentCollection(array $conf): ?AbstractComponentCollection
	{
		$collectionClass = $conf['componentCollection'] ?? '';

		if (empty($collectionClass)) {
			if ($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false) {
				throw new \RuntimeException(
					'No componentCollection specified in FLUIDCOMPONENT configuration',
					1734537600
				);
			}
			return null;
		}

		if (!class_exists($collectionClass)) {
			if ($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false) {
				throw new \RuntimeException(
					sprintf('Component collection class "%s" does not exist', $collectionClass),
					1734537601
				);
			}
			return null;
		}

		try {
			$collection = GeneralUtility::makeInstance($collectionClass);
			if (!$collection instanceof AbstractComponentCollection) {
				throw new \RuntimeException(
					sprintf(
						'Component collection class "%s" must extend %s',
						$collectionClass,
						AbstractComponentCollection::class
					),
					1734537602
				);
			}
			return $collection;
		} catch (\Exception $e) {
			if ($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false) {
				throw $e;
			}
			return null;
		}
	}

	/**
	 * Compiles all variables to be passed to the component
	 *
	 * Includes:
	 * - Raw data from content object
	 * - Resolved record object
	 * - Variables from data processors
	 * - Variables from TypoScript configuration
	 *
	 * @param array $conf TypoScript configuration
	 * @return array All variables for component rendering
	 */
	protected function getContentObjectVariables(array $conf): array
	{
		$variables = [];
		$reservedVariables = ['data', 'record'];

		// Add raw data
		$variables['data'] = $this->cObj->data;

		// Add resolved record object
		$table = $this->cObj->getCurrentTable();
		if (!empty($table) && !empty($variables['data'])) {
			$variables['record'] = $this->recordFactory->createResolvedRecordFromDatabaseRow(
				$table,
				$variables['data']
			);
		}

		// Process data processors and merge results
		$variables = $this->getProcessedData($conf, $variables);

		// Add variables from TypoScript
		$configuredVariables = $this->getConfiguredVariables($conf);
		foreach ($configuredVariables as $variableName => $value) {
			if (!in_array($variableName, $reservedVariables, true)) {
				$variables[$variableName] = $value;
			}
		}

		return $variables;
	}

	/**
	 * Gets variables defined via TypoScript variables property
	 *
	 * @param array $conf TypoScript configuration
	 * @return array Variables from TypoScript
	 */
	protected function getConfiguredVariables(array $conf): array
	{
		$variables = [];
		$variablesConf = $conf['variables.'] ?? [];

		foreach ($variablesConf as $variableName => $typoscript) {
			if (substr($variableName, -1) === '.') {
				continue;
			}
			$variableContentConf = $variablesConf[$variableName . '.'] ?? [];
			$variables[$variableName] = $this->cObj->cObjGetSingle($typoscript, $variableContentConf, 'variables.' . $variableName);
		}

		return $variables;
	}

	/**
	 * Processes data using configured data processors
	 *
	 * @param array $conf TypoScript configuration
	 * @param array $variables Initial variables
	 * @return array Processed variables
	 */
	protected function getProcessedData(array $conf, array $variables): array
	{
		if (!isset($conf['dataProcessing.'])) {
			return $variables;
		}

		return $this->contentDataProcessor->process($this->cObj, $conf, $variables);
	}

	/**
	 * Creates a Fluid rendering context
	 *
	 * @return RenderingContextInterface
	 */
	protected function createRenderingContext(): RenderingContextInterface
	{
		return $this->renderingContextFactory->create(
			[],
			$this->request
		);
	}
}