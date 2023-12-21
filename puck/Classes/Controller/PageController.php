<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use UBOS\Puckloader\Attribute\Plugin;

use UBOS\Puck\Domain\Repository\PageRepository;

/**
 * Page Controller.
 */
class PageController extends ActionController
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected ContentObjectRenderer $contentObjectRenderer,
    )
    {
    }

    #[Plugin("Page")]
    public function indexAction(): ResponseInterface
    {
        $data = $this->request->getAttribute('currentContentObject')->data;
        $context = GeneralUtility::makeInstance(Context::class);
        $contentObject = new ContentContentObject();
        $contentObject->setRequest($this->request);
        $contentObject->setContentObjectRenderer($this->contentObjectRenderer);
        $model = $this->pageRepository->findByUid($data['uid']);

        $variables = [];

        if (array_key_exists('dataProcessing', $this->settings)) {
            $contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
            $dataProcessingAsTypoScriptArray = GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
            $variables = $contentDataProcessor->process(
                $this->request->getAttribute('currentContentObject'),
                ['dataProcessing.' => $dataProcessingAsTypoScriptArray ?? null],
                ['data' => $data]
            );
        }

        $backendRows = [
            ['colPos' => 1, 'slide' => 0],
            ['colPos' => 3, 'slide' => -1],
            ['colPos' => 9, 'slide' => 0]
        ];

        // to do update, replace with alternative
        foreach($backendRows as $row) {
            $variables['contentElements']['colPos'.$row['colPos']] = $contentObject->render([
                'table' => 'tt_content',
                'select.' => [
                    'pidInList' => $data['uid'],
                    'where' => '{#colPos}='.$row['colPos'],
                    'orderBy' => 'sorting',
                ],
                'slide' => $row['slide']
            ]);
        }

        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        $frontendUserAspect = $context->getAspect('frontend.user');
        $variables['context'] = [
            'backendUser' => $context->getPropertyFromAspect('backend.user', 'username'),
            'timestamp'  => $context->getPropertyFromAspect('date', 'timestamp'),
            'site' => $site,
            'frontendUser' => [
                'username' => $frontendUserAspect->get('username'),
                'isLoggedIn' => $frontendUserAspect->get('isLoggedIn'),
                'isAdmin' => $frontendUserAspect->get('isAdmin'),
                'groupIds' => $frontendUserAspect->get('groupIds'),
                'groupNames' => $frontendUserAspect->get('groupNames'),
            ],
            'language' => $site->getLanguageById($context->getPropertyFromAspect('language', 'id')),
        ];

        $variables['settings'] = $this->settings;
        $variables['object'] = $model;
        $this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
        $this->view->assignMultiple(
            $variables
        );
        return $this->htmlResponse();

    }

}
