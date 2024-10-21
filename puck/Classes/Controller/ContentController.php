<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use UBOS\Puckloader\Attribute\Plugin;

class ContentController extends ActionController
{
    protected function processContentElementData()
    {
    }

    #[Plugin("Content")]
    public function indexAction(): ResponseInterface
    {
        $cObj = $this->request->getAttribute('currentContentObject');
        $recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $data = $cObj->data;
        $variables = [];
        $processed = [];
        if (array_key_exists('dataProcessing', $this->settings)) {
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
        $variables['record'] = $recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $data);
        $variables['object'] = $dataMapper->map($this->settings['modelNamespace'] . $this->settings['modelName'], [$data])[0];
        $context = $this->view->getRenderingContext();
        $context->setControllerAction($this->settings['modelName']);
        $this->view->setRenderingContext($context);
        $this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
        $this->view->setPartialRootPaths([$this->settings['view']['partialRootPath']]);
        $this->view->setLayoutRootPaths([$this->settings['view']['layoutRootPath']]);
        $this->view->assignMultiple(
            $variables
        );

        return $this->htmlResponse();
    }
}
