<?php

namespace UBOS\Puck\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class PersonRepository extends Repository
{
    /**
     * @return void
     */
    public function initializeObject() {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    );

    /**
     * @param array $settings
     * @return QueryResult
     */
    public function findByListSettings(array $settings) : QueryResult
    {
        $demand = $settings['demand'];
        $query = $this->createQuery();
        $constraints = [];
        $pidUidConstraints = [];
        if ($settings['persons']) {
            foreach (explode(',', $settings['persons']) as $key => $value) {
                $pidUidConstraints[] = $query->equals('uid', $value);
            }
        }
        if ($settings['parents']) {
            foreach (explode(',', $settings['parents']) as $key => $value) {
                $pidUidConstraints[] = $query->equals('pid', $value);
            }
        }
        if ($pidUidConstraints) {
            $constraints[] = $query->logicalOr($pidUidConstraints);
        }
        if ($demand['team']) {
            $constraints[] = $query->equals('is_team_member', 1);
        }
        if ($demand['limit']) {
            $query->setLimit((int)$demand['limit']);
        }
        if ($demand['offset']) {
            $query->setOffset((int)$demand['offset']);
        }
        if ($settings['order']['direction'] == 'asc') {
            $orderDirection = QueryInterface::ORDER_ASCENDING;
        } else {
            $orderDirection = QueryInterface::ORDER_DESCENDING;
        }
        $query->setOrderings([$settings['order']['field'] => $orderDirection, 'sorting' => QueryInterface::ORDER_ASCENDING]);
        return $query->matching($query->logicalAnd($constraints))->execute();
    }

}