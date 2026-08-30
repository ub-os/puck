<?php

declare(strict_types=1);

namespace UBOS\Puck\ContentObject;

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolverDelegateRegistry;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;

/**
 * FLUIDCOMPONENT content object — renders a Fluid component directly,
 * without requiring an intermediary template file.
 *
 * Supports both config-based (declarative) and class-based component collections
 * registered in TYPO3's ViewHelperResolverDelegateRegistry.
 *
 * Example TypoScript:
 *
 *   10 = FLUIDCOMPONENT
 *   10 {
 *     componentCollection = UBOS\Puck\Components\Modules
 *     component = teaserCard
 *     dataProcessing {
 *       10 = TYPO3\CMS\Frontend\DataProcessing\FilesProcessor
 *       10.references.fieldName = media
 *     }
 *     variables {
 *       myLabel = TEXT
 *       myLabel.value = Hello
 *     }
 *   }
 *
 * For nested components (e.g. atom.button), use dot notation:
 *
 *   component = atom.button
 */
class FluidComponentContentObject extends AbstractContentObject
{
	public function __construct(
		private readonly RenderingContextFactory $renderingContextFactory,
		private readonly ContentDataProcessor $contentDataProcessor,
		private readonly ViewHelperResolverDelegateRegistry $delegateRegistry,
	) {}

	public function render($conf = []): string
	{
		$componentName = trim($conf['component'] ?? '');
		$namespace = trim($conf['componentCollection'] ?? '');

		if ($componentName === '' || $namespace === '') {
			return '';
		}

		$delegate = $this->delegateRegistry->getAll()[$namespace] ?? null;
		if (!$delegate instanceof ComponentDefinitionProviderInterface) {
			if ($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false) {
				return sprintf(
					'<!-- FLUIDCOMPONENT ERROR: No component collection found for namespace "%s" -->',
					htmlspecialchars($namespace),
				);
			}
			return '';
		}

		$variables = $this->getContentObjectVariables($conf);
		$renderingContext = $this->renderingContextFactory->create([], $this->request);

		try {
			return $delegate->getComponentRenderer()->renderComponent(
				$componentName,
				$variables,
				[],
				$renderingContext,
			);
		} catch (\Exception $e) {
			if ($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false) {
				return sprintf(
					'<!-- FLUIDCOMPONENT ERROR: %s -->',
					htmlspecialchars($e->getMessage()),
				);
			}
			return '';
		}
	}

	/**
	 * Compiles all variables to pass to the component:
	 * - raw data array as {data}
	 * - results from dataProcessing
	 * - variables defined via TypoScript variables { }
	 *
	 * Use RecordTransformationProcessor in dataProcessing to get a {record} object.
	 */
	private function getContentObjectVariables(array $conf): array
	{
		$variables = [];
		$reservedVariables = ['data', 'current'];

		$variables['data'] = $this->cObj->data;

		// Data processors
		if (isset($conf['dataProcessing.'])) {
			$variables = $this->contentDataProcessor->process($this->cObj, $conf, $variables);
		}

		// TypoScript variables { } — mirrors core FluidTemplateContentObject pattern
		$variablesToProcess = (array)($conf['variables.'] ?? []);
		foreach ($variablesToProcess as $variableName => $cObjType) {
			if (is_array($cObjType)) {
				continue;
			}
			if (in_array($variableName, $reservedVariables, true)) {
				throw new \InvalidArgumentException(
					'Cannot use reserved variable name "' . $variableName . '" in FLUIDCOMPONENT variables.',
					1748511298,
				);
			}
			$variables[$variableName] = $this->cObj->cObjGetSingle(
				$cObjType,
				$variablesToProcess[$variableName . '.'] ?? [],
				'variables.' . $variableName,
			);
		}

		return $variables;
	}
}
