<?php

namespace UBOS\Puck\Domain\Repository\Page;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Repository;
use UBOS\Puck\Constants;

/**
 *
 */
class PageRepository extends Repository
{

    const ALLOWED_DOKTYPES = [
        1, 4, 7, 3,
        Constants::DOKTYPE_START,
        Constants::DOKTYPE_OVERVIEW,
        Constants::DOKTYPE_PERSON
    ];

    /**
     * @return void
     */
    public function initializeObject() {
        $querySettings = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);
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
     * @param array $options
     * @return QueryResult
     */
    public function findAll(array $options = []) : QueryResult
    {
        $options = array_merge(
            [
                'navHide' => 0,
                'limit' => 0,
            ],
            $options
        );
        $query = $this->createQuery();
        $constraints = [$query->in('doktype', self::ALLOWED_DOKTYPES)];
        if ($options['navHide']) {
            $constraints[] = $query->equals('nav_hide', 0);
        }
        if ($options['limit']) {
            $query->setLimit($options['limit']);
        }
        return $query->matching(
            $query->logicalAnd(
                $constraints
            )
        )->execute();
    }

    /**
     * @param string $uidList
     * @param string $pidList
     * @param array $options
     * @return QueryResult|array
     */
    public function findByUidListAndPidList(string $uidList, string $pidList, array $options = []): QueryResult|array
    {
        if (!$uidList && !$pidList) {
            return [];
        }
        $options = array_merge(
            [
                'navHide' => 0,
                'orderByUidList' => 0,
                'limit' => 0,
            ],
            $options
        );
        $query = $this->createQuery();
        $uidArray = explode(',', $uidList);
        $pidUidConstraints = [];
        foreach ($uidArray as $key => $value) {
            $pidUidConstraints[] = $query->equals('uid', $value);
        }
        if ($pidList !== '') {
            foreach (explode(',', $pidList) as $key => $value) {
                if ($options['navHide']) {
                    $pidUidConstraints[] = $query->logicalAnd(
                        $query->equals('pid', $value),
                        $query->equals('nav_hide', 0),
                    );
                } else {
                    $pidUidConstraints[] = $query->equals('pid', $value);
                }
            }
        }
        $constraints = array(
            $query->logicalOr(
                $pidUidConstraints
            ),
            $query->in('doktype', self::ALLOWED_DOKTYPES),
        );
        if ($options['limit']) {
            $query->setLimit($options['limit']);
        }
        $queryResult = $query->matching(
            $query->logicalAnd(
                $constraints
            )
        )->execute();
        if ($options['orderByUidList']) {
            $queryResult = $queryResult->toArray();
            usort($queryResult, function ($a, $b) use ($uidArray) {
                $pos_a = array_search($a->getUid(), $uidArray);
                $pos_b = array_search($b->getUid(), $uidArray);
                if ($pos_a === false && $pos_b === false) {
                    return false;
                }
                if ($pos_a === false) {
                    return true;
                }
                if ($pos_b === false) {
                    return false;
                }
                return $pos_a - $pos_b;
            });
        }
        return $queryResult;
    }

}