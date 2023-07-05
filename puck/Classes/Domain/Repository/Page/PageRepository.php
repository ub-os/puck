<?php

namespace UBOS\Puck\Domain\Repository\Page;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 *
 */
class PageRepository extends Repository
{
    public const DOKTYPE_START = 16501;
    public const DOKTYPE_OVERVIEW = 16502;
    public const DOKTYPE_POST = 16503;
    public const DOKTYPE_PERSON = 16504;
    public const DOKTYPE_DETAIL_PLUGIN = 16511;
    const ALLOWED_DOKTYPES = [
        1, 4, 7, 3,
        self::DOKTYPE_START,
        self::DOKTYPE_OVERVIEW,
        self::DOKTYPE_PERSON
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

    public function setPageObjectType($className) {
        $this->objectType = 'UBOS\\Puck\\Domain\\Model\\Page\\'.$className;
    }

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

    /**
     * @param array $settings
     * @return QueryResult
     */
    public function findByListSettings(array $settings, ?array $allowedDoktypes = null) : QueryResult
    {
        $demand = $settings['demand'];
        $query = $this->createQuery();
        $constraints = [$query->in('doktype', $allowedDoktypes ?? self::ALLOWED_DOKTYPES)];
        $pidUidConstraints = [];
        if ($settings['pages']) {
            foreach (explode(',', $settings['pages']) as $key => $value) {
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
        if ($demand['navHide']) {
            $constraints[] = $query->equals('nav_hide', 0);
        }
        if ($demand['author']) {
            $constraints[] = $query->equals('post_author', $demand['author']);
        }
        if ($demand['category']['list'] && $demand['category']['conjunction']) {
            $constraints[] = $this->createCategoryConstraint($query, $demand['category']['list'], $demand['category']['conjunction']);
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

    /**
     * Returns a category constraint created by
     * a given list of categories and a junction string
     *
     * @param QueryInterface $query
     * @param  array $categories
     * @param  string $conjunction
     * @return ConstraintInterface|null
     */
    protected function createCategoryConstraint(
        QueryInterface $query,
                       $categories,
        string $conjunction,
    ): ?ConstraintInterface {
        $constraint = null;
        $categoryConstraints = [];
        // If "ignore category selection" is used, nothing needs to be done
        if (empty($conjunction)) {
            return null;
        }
        if (!is_array($categories)) {
            $categories = GeneralUtility::intExplode(',', $categories, true);
        }
        foreach ($categories as $category) {
            $categoryConstraints[] = $query->contains('categories', $category);

        }
        if ($categoryConstraints) {
            $constraint = match (strtolower($conjunction)) {
                'or' => $query->logicalOr($categoryConstraints),
                'and' => $query->logicalAnd($categoryConstraints),
                'notor' => $query->logicalNot($query->logicalOr($categoryConstraints)),
                'notand' => $query->logicalNot($query->logicalAnd($categoryConstraints))
            };
        }
        return $constraint;
    }
}