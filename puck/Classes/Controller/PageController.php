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
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use SMS\FluidComponents\Utility\ComponentSettings;

use UBOS\Puck\Attribute\AsPlugin;

/**
 * Page Controller.
 */
#[AsPlugin("Page")]
class PageController extends ActionController
{
	public function __construct(
		protected ContentContentObject $contentContentObject,
		protected RecordFactory        $recordFactory,
		protected ComponentSettings    $componentSettings,
		protected PageRenderer         $pageRenderer,
		protected ViewFactoryInterface $viewFactory
	)
	{
	}

	public function indexAction(): ResponseInterface
	{
		$cObj = $this->request->getAttribute('currentContentObject');
		$data = $cObj->data;
		$site = $this->request->getAttribute('site');
		$siteSettings = $site->getSettings();
		$context = GeneralUtility::makeInstance(Context::class);
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

		// set settings for all fluid components
		$this->componentSettings
			->set('template', $siteSettings->get('template'))
			->set('navigation', $siteSettings->get('navigation'))
			->set('doktypes', $siteSettings->get('doktypes'));

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
		$variables['context'] = [
			'backendUser' => $context->getPropertyFromAspect('backend.user', 'username'),
			'site' => $site,
			'language' => $site->getLanguageById($context->getPropertyFromAspect('language', 'id')),
		];

		$this->pageRenderer->addHeaderData($this->renderFaviconHeadTags($siteSettings));
		$this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
		$this->view->assignMultiple($variables);
		return $this->htmlResponse();
	}


	protected function renderFaviconHeadTags($siteSettings): string
	{
		$faviconPath = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName(
			'EXT:puck/Resources/Public/Icons/Favicons/packages/'
			. ($siteSettings->get('template.favicon') ?? 'default')
		));
		$view = $this->viewFactory->create(
			new ViewFactoryData(
				templateRootPaths: ['EXT:puck/Resources/Private/Fluid/Page/Head'],
				request: $this->request,
			)
		);
		$view->assign('faviconPath', $faviconPath);
		return $view->render('FaviconTags');
	}
}
