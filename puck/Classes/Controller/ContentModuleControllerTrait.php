<?php

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use UBOS\Puck\Utility\PuckUtility;
use SMS\FluidComponents\Fluid\ViewHelper\ComponentRenderer;

trait ContentModuleControllerTrait
{
	protected RequestInterface $request;
	protected $view;
	protected array $viewVariables = [];
	protected array $settings;
	protected string $defaultFluidComponentNamespace = 'UBOS\Puck\Modules\\';

	protected function prepareContentView($flexFormConvertZeroStringsToInteger = true): void
	{
		$cObj = $this->request->getAttribute('currentContentObject');
		$data = $cObj->data;

		if ($this->settings['dataProcessing'] ?? false) {
			$processor = GeneralUtility::makeInstance(ContentDataProcessor::class);
			$processingTypoScript = GeneralUtility::makeInstance(TypoScriptService::class)
				->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
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

		if (($this->settings['flexFormFields'] ?? false) && $flexFormConvertZeroStringsToInteger) {
			foreach (explode(',', $this->settings['flexFormFields']) as $fieldName) {
				if ($this->viewVariables[$fieldName] ?? false) {
					$this->viewVariables[$fieldName] = PuckUtility::convertZeroStringsToInteger(
						$this->viewVariables[$fieldName]
					);
				}
			}
		}
		if ($flexFormConvertZeroStringsToInteger) {
			$this->settings = PuckUtility::convertZeroStringsToInteger($this->settings);
		}

		if ($this->settings['model'] ?? false) {
			$dataMapper = GeneralUtility::makeInstance(DataMapper::class);
			$this->viewVariables['record'] = $dataMapper->map($this->settings['model'], [$data])[0];
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

	protected function renderFluidComponent(
		?string $namespace = null,
		?array $arguments = null,
	): string {
		$namespace = $namespace ?? $this->defaultFluidComponentNamespace . ($this->settings['templateName'] ?? ucfirst($this->view->getRenderingContext()->getControllerAction()));
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