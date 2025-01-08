<?php

namespace UBOS\Puck\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Context\Context;

class CategoryRepository extends Repository
{
	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
	);

	/**
	 * @return void
	 */
	public function initializeObject(): void
	{
		$querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
		$querySettings->setRespectStoragePage(false);
		$this->setDefaultQuerySettings($querySettings);
	}

	public function findByUidList(string $uids): ?QueryResult
	{
		if (!$uids) {
			return null;
		}
		$languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
		$query = $this->createQuery();
		return $query
			->matching(
				$query->logicalAnd(
					$query->logicalOr(
						$query->in('l10n_parent', explode(',', $uids)),
						$query->in('uid', explode(',', $uids))
					),
					$query->in('sys_language_uid', [-1, $languageAspect->getId()])
				)
			)
			->execute();
	}
}