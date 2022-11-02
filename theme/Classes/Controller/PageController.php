<?php

/**
 * Page Controller.
 */
declare(strict_types=1);

namespace UBOS\Theme\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Page Controller.
 */
class PageController extends ActionController
{
    /**
     * Render the Page via ExtBase.
     */
    public function indexAction(): string
    {
        try {
            $data = $this->configurationManager->getContentObject()->data;
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            $contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $contentObject = new ContentContentObject($contentObjectRenderer);
            $modelArray = $dataMapper->map('UBOS\Theme\Domain\Model\Page', [$data]);
            $colPoss = [1,2,3];
            $contentElements = [];
            foreach($colPoss as $colPos) {
                $contentElements['colPos'.$colPos] = $contentObject->render([
                    'table' => 'tt_content',
                    'select.' => [
                        'pidInList' => $data['uid'],
                        'where' => '{#colPos}='.$colPos,
                        'orderBy' => 'sorting',
                    ]
                ]);
            }
            $variables = $contentDataProcessor->process(
                $this->configurationManager->getContentObject(),
                ['dataProcessing.' => $this->settings['dataProcessing'] ?? null],
                ['data' => $data]
            );
            $variables['settings'] = $this->settings;
            $variables['object'] = $modelArray[0];
            $variables['contentElements'] = $contentElements;
            $this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
            $this->view->assignMultiple(
                $variables
            );
            return $this->view->render();
        } catch (\Exception $ex) {
            return 'Exception in content rendering: ' . $ex->getMessage();
        }
    }
}
