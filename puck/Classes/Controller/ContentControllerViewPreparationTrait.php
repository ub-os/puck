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

trait ContentControllerViewPreparationTrait
{
	protected RequestInterface $request;
	protected $view;
	protected array $viewVariables = [];
	protected array $settings;

	protected function prepareContentView($flexFormConvertZeroStringsToInteger = true): void
	{
		$cObj = $this->request->getAttribute('currentContentObject');
		$data = $cObj->data;

		if ($this->settings['dataProcessing'] ?? false) {
			$processor = GeneralUtility::makeInstance(ContentDataProcessor::class);
			$processingTypoScript = GeneralUtility::makeInstance(TypoScriptService::class)
				->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
			$this->viewVariables['processed'] = $processor->process(
				$cObj,
				['dataProcessing.' => $processingTypoScript ?? null],
				['data' => $data]
			);
			unset($this->viewVariables['processed']['data']);
		}

		if (($this->settings['flexFormFields'] ?? false) && $flexFormConvertZeroStringsToInteger) {
			foreach (explode(',', $this->settings['flexFormFields']) as $fieldName) {
				if ($this->viewVariables['processed'][$fieldName] ?? false) {
					$this->viewVariables['processed'][$fieldName] = PuckUtility::convertZeroStringsToInteger(
						$this->viewVariables['processed'][$fieldName]
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

		$this->setContentTemplatePath();
		$this->view->assignMultiple($this->viewVariables);
		$this->view->assign('settings', $this->settings);
	}

	protected function setContentTemplatePath(): void
	{
		$this->view->setTemplateRootPaths(['EXT:puck/Resources/Private/Fluid/Content/']);
	}
}