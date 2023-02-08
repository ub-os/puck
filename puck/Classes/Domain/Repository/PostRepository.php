<?php

namespace UBOS\Puck\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;

/**
 *
 */
class PostRepository extends PageRepository
{
    /**
     * @var array|int[]
     */
    protected array $allowedDoktypes = [60];
    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'post_date' => QueryInterface::ORDER_DESCENDING
    );

    /**
     * @param array $settings
     * @return QueryResult
     */
    public function findByListSettings(array $settings) : QueryResult
    {
        $demand = $settings['demand'];
        $query = $this->createQuery();
        $constraints = [$query->in('doktype', $this->allowedDoktypes)];
        $pidUidConstraints = [];
        if ($settings['posts']) {
            foreach (explode(',', $settings['posts']) as $key => $value) {
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
        $query->setOrderings([$settings['order']['field'] => $orderDirection]);
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