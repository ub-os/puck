<?php

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;

/**
 * Controller trait for content element plugins rendering Fluid components,
 * specifically for CTypes configured with @see \UBOS\Puck\Configuration\ContentElementConfiguration
 *
 * Provides methods to prepare the view (map data to record, run data processing) based on settings
 * and directly render Fluid components.
 */
trait ComponentContentElementTrait
{
	protected RequestInterface $request;
	protected $view;
	protected array $viewVariables = [];
	protected array $settings;

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
		}

		$recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
		$this->viewVariables['record'] = $recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $data);

		$this->view->assignMultiple($this->viewVariables);
	}


	/**
	 * Directly render a Fluid component
	 * @param string|null $component name of the component, defaults to '{settings[contentElementConfiguration][component] ?? controllerAction}'
	 * @param array|null $arguments Arguments to pass to the component, defaults to viewVariables
	 * @param AbstractComponentCollection|null $componentCollection Component collection to use for rendering, defaults to settings[contentElementConfiguration][componentCollection]
	 * @return string Rendered component
	 */
	protected function renderFluidComponent(
		?string $component = null,
		?array $arguments = null,
		?AbstractComponentCollection $componentCollection = null
	): string {
		$component = $component ?? ($this->settings['contentElementConfiguration']['component'] ?? lcfirst($this->view->getRenderingContext()->getControllerAction()));
		$arguments = $arguments ?? $this->viewVariables;
		$componentCollection = $componentCollection ?? GeneralUtility::makeInstance($this->settings['contentElementConfiguration']['componentCollection']);
		$componentRenderer = $componentCollection->getComponentRenderer();
		return $componentRenderer->renderComponent(
			$component,
			$arguments,
			[],
			$this->view->getRenderingContext()
		);
	}
}