<?php

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puck\Attribute\AsAction;

/**
 * Controller for the FileMenu plugin.
 * Creates a menu of files based on selected files and file collections in content record.
 */
class FileMenuController extends ActionController
{
	use ComponentContentElementTrait;

	public function __construct(
		protected ResourceFactory $resourceFactory
	)
	{
	}

	#[AsAction("FileMenu")]
	public function fileMenuAction(): ResponseInterface
	{
		$variables = $this->getProcessedData();
		$record = $variables['record'];
		$menu = [];
		$cacheTags = [];
		foreach ($record->get('media') as $file) {
			$menu[$file->getIdentifier()] = $file;
			$cacheTags['sys_file_' . $file->getUid()] = true;
		}
		foreach ($record->get('file_collections') as $collectionRecord) {
			$cacheTags['sys_file_collection_' . $collectionRecord->getUid()] = true;
			$collectionDomainObject = $this->resourceFactory->createCollectionObject($collectionRecord->getRawRecord()->toArray());
			if (!$collectionDomainObject instanceof AbstractFileCollection) {
				continue;
			}
			$collectionDomainObject->loadContents();
			foreach ($collectionDomainObject->getItems() as $file) {
				$menu[$file->getIdentifier()] = $file;
				$cacheTags['sys_file_' . $file->getUid()] = true;
			}
		}
		$this->request->getAttribute('frontend.cache.collector')?->addCacheTags(
			...array_map(static fn(string $tag) => new CacheTag($tag), array_keys($cacheTags))
		);
		$variables['menu'] = $this->sortMenu(
			$menu,
			$record->get('filelink_sorting'),
			$record->get('filelink_sorting_direction')
		);
		return $this->htmlResponse(
			$this->renderComponent($variables)
		);
	}

	protected function sortMenu(array $menu, string $filelinkSorting, string $filelinkSortingDirection): array
	{
		if ($filelinkSorting === '') {
			return $menu;
		}
		$descending = strtolower($filelinkSortingDirection) === 'desc';
		usort($menu, static function ($a, $b) use ($filelinkSorting, $descending): int {
			$valA = $a->getProperties()[$filelinkSorting] ?? null;
			$valB = $b->getProperties()[$filelinkSorting] ?? null;
			$result = is_string($valA) || is_string($valB)
				? strcasecmp((string)$valA, (string)$valB)
				: $valA <=> $valB;
			return $descending ? -$result : $result;
		});
		return $menu;
	}
}