<?php

/**
 * Page Controller.
 */
declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Core\Utility\RootlineUtility;
//use UBOS\Puck\Domain\Repository\PageRepository;
//use B13\Menus\Domain\Repository\MenuRepository;


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
            $context = GeneralUtility::makeInstance(Context::class);
            $contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $contentObject = new ContentContentObject($contentObjectRenderer);
            $model = $dataMapper->map('UBOS\Puck\Domain\Model\Page', [$data])[0];
            $backendRows = [
                ['colPos' => 1, 'slide' => 0],
                ['colPos' => 3, 'slide' => -1],
                ['colPos' => 9, 'slide' => 0]
            ];
            $contentElements = [];
            foreach($backendRows as $row) {
                $contentElements['colPos'.$row['colPos']] = $contentObject->render([
                    'table' => 'tt_content',
                    'select.' => [
                        'pidInList' => $data['uid'],
                        'where' => '{#colPos}='.$row['colPos'],
                        'orderBy' => 'sorting',
                    ],
                    'slide' => $row['slide']
                ]);
            }
            $variables = $contentDataProcessor->process(
                $this->configurationManager->getContentObject(),
                ['dataProcessing.' => $this->settings['dataProcessing'] ?? null],
                ['data' => $data]
            );
            $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
            $variables['context'] = [
                'backendUser' => $context->getPropertyFromAspect('backend.user', 'username'),
                'timestamp'  => $context->getPropertyFromAspect('date', 'timestamp'),
                'site' => $site,
                'language' => $site->getLanguageById($context->getPropertyFromAspect('language', 'id')),
            ];
            $variables['settings'] = $this->settings;
            $variables['object'] = $model;
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
