<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use SMS\FluidComponents\Utility\ComponentSettings;

use UBOS\Puckloader\Attribute\Plugin;

/**
 * Page Controller.
 */
class PageController extends ActionController
{
    public function __construct(
        protected ContentContentObject $contentContentObject,
        protected RecordFactory        $recordFactory,
        protected ComponentSettings    $componentSettings,
        protected PageRenderer         $pageRenderer
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
        foreach ($backendRows as $row) {
            $variables['contentElements']['colPos' . $row['colPos']] = $this->contentContentObject->render([
                'table' => 'tt_content',
                'select.' => [
                    'pidInList' => $data['uid'],
                    'where' => '{#colPos}=' . $row['colPos'],
                    'orderBy' => 'sorting',
                ],
                'slide' => $row['slide']
            ]);
        }

        $site = $this->request->getAttribute('site');
        $siteSettings = $site->getSettings();
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

        // set settings for all fluid components
        $this->componentSettings
            ->set('template', $siteSettings->get('template'))
            ->set('navigation', $siteSettings->get('navigation'))
            ->set('doktypes', $siteSettings->get('doktypes'));
        $this->pageRenderer->addHeaderData($this->getFaviconHeadTags($siteSettings));
        $this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
        $this->view->assignMultiple($variables);
        return $this->htmlResponse();
    }


    protected function getFaviconHeadTags($siteSettings): string
    {
        $faviconPath = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName(
            'EXT:puck/Resources/Public/Icons/Favicons/packages/'
            . $siteSettings->get('template.favicon')
        ));
        return '<link rel="icon" type="image/x-icon" href="' . $faviconPath . '/favicon.ico"><link rel="icon" type="image/png" sizes="16x16" href="' . $faviconPath . '/favicon-16x16.png"><link rel="icon" type="image/png" sizes="32x32" href="' . $faviconPath . '/favicon-32x32.png"><link rel="icon" type="image/png" sizes="48x48" href="' . $faviconPath . '/favicon-48x48.png"><meta name="theme-color" content="#fff"><link rel="apple-touch-icon" sizes="57x57" href="' . $faviconPath . '/apple-touch-icon-57x57.png"><link rel="apple-touch-icon" sizes="60x60" href="' . $faviconPath . '/apple-touch-icon-60x60.png"><link rel="apple-touch-icon" sizes="72x72" href="' . $faviconPath . '/apple-touch-icon-72x72.png"><link rel="apple-touch-icon" sizes="76x76" href="' . $faviconPath . '/apple-touch-icon-76x76.png"><link rel="apple-touch-icon" sizes="114x114" href="' . $faviconPath . '/apple-touch-icon-114x114.png"><link rel="apple-touch-icon" sizes="120x120" href="' . $faviconPath . '/apple-touch-icon-120x120.png"><link rel="apple-touch-icon" sizes="144x144" href="' . $faviconPath . '/apple-touch-icon-144x144.png"><link rel="apple-touch-icon" sizes="152x152" href="' . $faviconPath . '/apple-touch-icon-152x152.png"><link rel="apple-touch-icon" sizes="167x167" href="' . $faviconPath . '/apple-touch-icon-167x167.png"><link rel="apple-touch-icon" sizes="180x180" href="' . $faviconPath . '/apple-touch-icon-180x180.png"><link rel="apple-touch-icon" sizes="1024x1024" href="' . $faviconPath . '/apple-touch-icon-1024x1024.png">';
    }
}
