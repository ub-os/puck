<?php

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;

trait ContentControllerDataProcessingTrait
{
    protected RequestInterface $request;
    protected $view;
    protected array $settings;
    protected function prepareVariables(): array
    {
        $cObj = $this->request->getAttribute('currentContentObject');
        $data = $cObj->data;
        $variables = [];
        if ($this->settings['dataProcessing'] ?? false) {
            $processor = GeneralUtility::makeInstance(ContentDataProcessor::class);
            $processingTypoScript = GeneralUtility::makeInstance(TypoScriptService::class)
                ->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
            $variables['processed'] = $processor->process(
                $cObj,
                ['dataProcessing.' => $processingTypoScript ?? null],
                ['data' => $data]
            );
            unset($variables['processed']['data']);
        }
        if ($this->settings['model'] ?? false) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            $variables['record'] = $dataMapper->map($this->settings['model'], [$data])[0];
        } else {
            $recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
            $variables['record'] = $recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $data);
        }
        return $variables;
    }
    protected function setContentTemplatePath(): void
    {
        $this->view->setTemplateRootPaths(['EXT:puck/Resources/Private/Fluid/Content/']);
    }
}