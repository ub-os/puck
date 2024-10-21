<?php

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;

trait ContentControllerTrait
{
    protected RequestInterface $request;
    protected array $settings;
    protected function prepareVariables(): array
    {
        $cObj = $this->request->getAttribute('currentContentObject');
        $data = $cObj->data;
        $variables = [];
        $processed = [];
        if ($this->settings['dataProcessing'] ?? false) {
            DebugUtility::debug($this->settings['dataProcessing']);
            $processor = GeneralUtility::makeInstance(ContentDataProcessor::class);
            $processingTypoScript = GeneralUtility::makeInstance(TypoScriptService::class)
                ->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
            $processed = $processor->process(
                $cObj,
                ['dataProcessing.' => $processingTypoScript ?? null],
                ['data' => $data]
            );
            unset($processed['data']);
        }
        $variables['processed'] = $processed;
        if ($this->settings['model'] ?? false) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            $variables['record'] = $dataMapper->map($this->settings['model'], [$data])[0];
        } else {
            $recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
            $variables['record'] = $recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $data);
        }
        return $variables;
    }
}