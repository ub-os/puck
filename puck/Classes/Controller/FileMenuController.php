<?php

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puck\Attribute\AsPlugin;

#[AsPlugin("FileMenu")]
class FileMenuController extends ActionController
{
	use ContentModuleControllerTrait;

	public function __construct(
		protected ResourceFactory $resourceFactory
	)
	{
	}

	public function fileMenuAction(): ResponseInterface
	{
		$this->prepareContentView();
		$record = $this->viewVariables['record'];
		$menu = [];
		foreach ($record->get('media') as $file) {
			$menu[$file->getIdentifier()] = $file;
		}
		foreach ($record->get('file_collections') as $collectionRecord) {
			$collectionDomainObject = $this->resourceFactory->createCollectionObject($collectionRecord->getRawRecord()->toArray());
			$collectionDomainObject->loadContents();
			foreach ($collectionDomainObject->getItems() as $file) {
				$menu[$file->getIdentifier()] = $file;
			}
		}
		$menu = $this->sortMenu(
			$menu,
			$record->get('filelink_sorting'),
			$record->get('filelink_sorting_direction')
		);
		$this->viewVariables['menu'] = $menu;
		return $this->htmlResponse(
			$this->renderFluidComponent()
		);
	}

	protected function sortMenu(array $menu, string $filelinkSorting, string $filelinkSortingDirection): array
	{
		if ($filelinkSorting) {
			usort($menu, function ($a, $b) use ($filelinkSorting, $filelinkSortingDirection) {
				$valA = $a->getProperties()[$filelinkSorting];
				$valB = $b->getProperties()[$filelinkSorting];
				if ($filelinkSortingDirection === 'desc') {
					if (is_string($valA)) {
						return strcasecmp($valA, $valB);
					}
					return $valA < $valB;
				} else {
					if (is_string($valA)) {
						return strcasecmp($valB, $valA);
					}
					return $valA > $valB;
				}
			}
			);
		}
		return $menu;
	}
}