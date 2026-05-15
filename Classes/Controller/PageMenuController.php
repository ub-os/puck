<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Amdeu\MenuControls\Controller\MenuController;
use UBOS\Puck\Attribute\AsAction;
use UBOS\Puck\Domain\Repository\PageTeaserRepository;

/**
 * Controller for the PageMenu plugin.
 * Creates a menu of pages records based on MenuDemand created from the plugin settings.
 * Will also build CategoryFilter and Pagination objects
 * and override teaser properties of menu items with values from teaser records if configured in settings.
 * @see \UBOS\Puck\Record\PageRecord::overrideWithTeaser()
 */
class PageMenuController extends MenuController
{
	use ComponentContentElementTrait;

	#[AsAction("PageMenu")]
	public function pageMenuAction(): ResponseInterface
	{
		$variables = $this->getProcessedData();
		$menuVariables = $this->buildDemandMenuWithControls($this->pageRepository, 'pageMenu');
		$pageRecords = $this->pageRepository->mapToRecords($menuVariables['records']);

		// apply teaser overrides to page records
		if ($this->settings['pageTeasers'] ?? false) {
			$teaserRepository = GeneralUtility::makeInstance(PageTeaserRepository::class);
			$teasers = $teaserRepository->findByUidList($this->settings['pageTeasers']);
			$teasersByPageUid = [];
			foreach ($teasers as $teaser) {
				$teasersByPageUid[$teaser->page] = $teaser;
			}
			foreach ($pageRecords as $pageRecord) {
				if ($teaser = $teasersByPageUid[$pageRecord->getUid()] ?? null) {
					$pageRecord->overrideWithTeaser($teaser);
				}
			}
		}

		$variables = [
			...$variables,
			...[
				'menu' => $pageRecords,
				'pagination' => $menuVariables['pagination'] ?? null,
				'categoryFilter' => $menuVariables['categoryFilters']['main'] ?? null
			]
		];
		return $this->htmlResponse(
			$this->renderComponent($variables)
		);
	}


//	protected function buildFragmentUrl(string $actionName, array $overrides): string
//	{
//		$args = $this->request->getArguments();
//		$overrides['ceUid'] = $this->request->getAttribute('currentContentObject')?->data['uid'] ?? null;
//		return $this->uriBuilder
//			->reset()
//			->setTargetPageUid($this->request->getAttribute('routing')->getPageId())
//			->setTargetPageType(1619409324)
//			->setCreateAbsoluteUri(false)
//			->uriFor($actionName, $this->removeNullValues(array_replace_recursive($args, $overrides)));
//	}

}
