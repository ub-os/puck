<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;

use UBOS\Puckloader\Attribute\Plugin;

/**
 * Page Controller.
 */
class PageController extends ActionController
{
    public function __construct(
        protected ContentContentObject $contentContentObject,
        protected RecordFactory $recordFactory
    )
    {
    }

    #[Plugin("Page")]
    public function indexAction(): ResponseInterface
    {
        $cObj = $this->request->getAttribute('currentContentObject');
        $data = $cObj->data;
        $variables = [];
        $variables['settings'] = $this->settings;
        $variables['record'] = $this->recordFactory->createResolvedRecordFromDatabaseRow('pages', $data);

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

        $backendRows = [
            ['colPos' => 1, 'slide' => 0],
            ['colPos' => 3, 'slide' => -1],
            ['colPos' => 9, 'slide' => 0]
        ];

        // to do update, replace with alternative
        $this->contentContentObject->setRequest($this->request);
        $this->contentContentObject->setContentObjectRenderer($cObj);
        foreach($backendRows as $row) {
            $variables['contentElements']['colPos'.$row['colPos']] = $this->contentContentObject->render([
                'table' => 'tt_content',
                'select.' => [
                    'pidInList' => $data['uid'],
                    'where' => '{#colPos}='.$row['colPos'],
                    'orderBy' => 'sorting',
                ],
                'slide' => $row['slide']
            ]);
        }

        $site = $this->request->getAttribute('site');
        $context = GeneralUtility::makeInstance(Context::class);
        $frontendUserAspect = $context->getAspect('frontend.user');
        $variables['context'] = [
            'backendUser' => $context->getPropertyFromAspect('backend.user', 'username'),
            'site' => $site,
            'frontendUser' => [
                'isLoggedIn' => $frontendUserAspect->get('isLoggedIn'),
            ],
            'language' => $site->getLanguageById($context->getPropertyFromAspect('language', 'id')),
        ];

        $this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
        $this->view->assignMultiple($variables);
        return $this->htmlResponse();
    }
}
