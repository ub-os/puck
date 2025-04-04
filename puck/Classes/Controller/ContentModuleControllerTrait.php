<?php

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;

/**
 * Controller trait for content element plugins
 * specifically for CTypes configured with @see \UBOS\Puck\Configuration\ContentElementConfiguration
 *
 * Provides methods to prepare the view (map data to model or record, run data processing) based on settings
 * and directly render Fluid components.
 */
trait ContentModuleControllerTrait
{
	protected RequestInterface $request;
	protected $view;
	protected array $viewVariables = [];
	protected array $settings;
	protected string $defaultFluidComponentNamespace = 'UBOS\Puck\Modules\\';

	/**
	 * Run data processing and populate viewVariables
	 */
	protected function prepareContentView(): void
	{
		$cObj = $this->request->getAttribute('currentContentObject');
		$data = $cObj->data;

		$contentElementConfiguration = $this->settings['contentElementConfiguration'] ?? [];
		if ($contentElementConfiguration['dataProcessing'] ?? false) {
			$processor = GeneralUtility::makeInstance(ContentDataProcessor::class);
			$processingTypoScript = GeneralUtility::makeInstance(TypoScriptService::class)
				->convertPlainArrayToTypoScriptArray($contentElementConfiguration['dataProcessing']);
			$this->viewVariables = $processor->process(
				$cObj,
				['dataProcessing.' => $processingTypoScript ?? null],
				['data' => $data]
			);
			unset($this->viewVariables['data']);
		}

		// Fluid component arguments must be camelCase
		foreach ($this->viewVariables as $key => $value) {
			if (str_contains($key, '_')) {
				$this->viewVariables[GeneralUtility::underscoredToLowerCamelCase($key)] = $value;
				unset($this->viewVariables[$key]);
			}
		}

		if ($contentElementConfiguration['model'] ?? false) {
			$dataMapper = GeneralUtility::makeInstance(DataMapper::class);
			$this->viewVariables['record'] = $dataMapper->map($contentElementConfiguration['model'], [$data])[0];
		} else {
			$recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
			$this->viewVariables['record'] = $recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $data);
		}

		$this->viewVariables['settings'] = $this->settings;
		$this->view->assignMultiple($this->viewVariables);
		$this->setContentTemplatePath();
	}

	protected function setContentTemplatePath(): void
	{
		$this->view->setTemplateRootPaths(['EXT:puck/Resources/Private/Fluid/Content/']);
	}

	/**
	 * Directly render a Fluid component
	 * @param string|null $namespace Namespace of the component, defaults to 'UBOS\Puck\Modules\{settings[templateName] ?? controllerAction}'
	 * @param array|null $arguments Arguments to pass to the component, defaults to viewVariables
	 * @return string Rendered component
	 */
	protected function renderFluidComponent(
		?string $namespace = null,
		?array $arguments = null,
	): string {
		$namespace = $namespace ?? $this->defaultFluidComponentNamespace . ($this->settings['contentElementConfiguration']['templateName'] ?? ucfirst($this->view->getRenderingContext()->getControllerAction()));
		$arguments = $arguments ?? $this->viewVariables;
		$componentRenderer = GeneralUtility::makeInstance(ComponentRenderer::class);
		$componentRenderer->setComponentNamespace($namespace);
		$renderingContext = $this->view->getRenderingContext();

		// argument 'content' is required by the component renderer
		$arguments['content'] = $arguments['content'] ?? '';
		return $renderingContext->getViewHelperInvoker()->invoke(
			$componentRenderer,
			$arguments,
			$renderingContext,
		);
	}
}