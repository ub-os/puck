<?php

namespace UBOS\Puck\Menu;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use UBOS\Puck\Menu\Dto\MenuDemand;

trait FindByMenuDemand
{
    abstract public function createQuery();
    public function additionalMenuDemandConstraints(QueryInterface $query, array $settings): array
    {
        return [];
    }

    public function findByMenuDemand(MenuDemand $demand) : array
    {
        $query = $this->createQuery();
        $constraints = $this->additionalMenuDemandConstraints($query, $demand->additionalSettings);
        $pidUidConstraints = [];
        if ($demand->records) {
            foreach (explode(',', $demand->records) as $key => $value) {
                $pidUidConstraints[] = $query->equals('uid', $value);
            }
        }
        if ($demand->parents) {
            foreach (explode(',', $demand->parents) as $key => $value) {
                $pidUidConstraints[] = $query->equals('pid', $value);
            }
        }
        if ($pidUidConstraints) {
            // to do update, logicalOr needs multiple arguments of type ConstraintInterface
            $constraints[] = $query->logicalOr(...$pidUidConstraints);
        }

        $categoriesConstraints = [];
        foreach ($demand->categories as $key => $group) {
            if ($group['uids'] ?? '') {
                $categoriesConstraints[] = $this->createCategoryConstraint($query, $group['uids'] ?? '', $group['conjunction'] ?? 'or');
            }
        }
        if ($categoriesConstraints) {
            $constraints[] = match (strtolower($demand->categoriesConjunction)) {
                'or' => $query->logicalOr(...$categoriesConstraints),
                'and' => $query->logicalAnd(...$categoriesConstraints),
                'notor' => $query->logicalNot($query->logicalOr(...$categoriesConstraints)),
                'notand' => $query->logicalNot($query->logicalAnd(...$categoriesConstraints))
            };
        }

        if ($demand->limit) {
            $query->setLimit($demand->limit);
        }
        if ($demand->offset) {
            $query->setOffset($demand->offset);
        }
        $orderDirection = $demand->orderDirection === 'desc' ? QueryInterface::ORDER_DESCENDING : QueryInterface::ORDER_ASCENDING;
        $query->setOrderings([$demand->orderField => $orderDirection, 'sorting' => $orderDirection]);
        if (!$constraints) {
            $constraints[] = $query->greaterThan('uid', 0);
        }

        // to do update, logicalAnd needs multiple arguments of type ConstraintInterface
        $records = $query->matching($query->logicalAnd(...$constraints))->execute();

        if ($demand->orderByRecordsProperty) {
            $recordsArray = $records->toArray();
            $recordUids = explode(',', $demand->records);
            $notInUidsIterator = 0;
            $newArray = [];
            foreach ($recordsArray as $record) {
                $selectionPosition = array_search($record->getUid(), $recordUids);
                if ($selectionPosition !== false) {
                    $newArray[$selectionPosition] = $record;
                } else {
                    $newArray[count($recordUids) + $notInUidsIterator] = $record;
                    $notInUidsIterator++;
                }
            }
            ksort($newArray);
            return $newArray;
        }
        return $records->toArray();
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
        string|array $categories,
        string $conjunction,
    ): ?ConstraintInterface
    {
        $constraint = null;
        $categoryConstraints = [];

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
            // to do update, logicalAnd needs multiple arguments of type ConstraintInterface
            $constraint = match (strtolower($conjunction)) {
                'or' => $query->logicalOr(...$categoryConstraints),
                'and' => $query->logicalAnd(...$categoryConstraints),
                'notor' => $query->logicalNot($query->logicalOr(...$categoryConstraints)),
                'notand' => $query->logicalNot($query->logicalAnd(...$categoryConstraints))
            };
        }
        return $constraint;
    }
}