<?php

namespace UBOS\Theme\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

use TYPO3\CMS\Core\Utility\DebugUtility;


class PageRepository extends Repository
{
    public function initializeObject() {
        $querySettings = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    protected $defaultOrderings = array(
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    );

    public function getPagesAndSubPages($uidList, $pidList, $orderByUids = false) {
        $query = $this->createQuery();
        $constraints = [];
        $uidArray = explode(',', $uidList);
        foreach ($uidArray as $key => $value) {
            $constraints[] = $query->equals('uid', $value);
        }
        foreach (explode(',', $pidList) as $key => $value) {
            $constraints[] = $query->equals('pid', $value);
        }
        $queryResult = $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $constraints
                ),
                $query->lessThan('doktype', 100)
            )
        )->execute();
        $result = $queryResult->toArray();
        if ($orderByUids) {
            usort($result, function ($a, $b) use ($uidArray) {
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
        return $result;
    }

    public function getBreadcrumbsMenu($uid) {
        return GeneralUtility::makeInstance(DataMapper::class)
            ->map(
                $this->objectType,
                array_reverse(GeneralUtility::makeInstance(RootlineUtility::class, $uid)->get())
            );
    }

    public function getPageTree(int $uid, int $depth, $includeStartingPage = false)
    {
        $query = $this->createQuery();
        $pageTree = $query->matching(
            $query->logicalAnd(
                $query->equals('pid', $uid),
                $query->lessThan('doktype', 100)
            )
        )->execute()->toArray();
        foreach ($pageTree as $k => &$page) {
            if ($depth > 0) {
                $page->subpages = $this->getPageTree((int)$page->getUid(), $depth-1);
            }
        }
        if ($includeStartingPage) {
            $startingPage = $this->findByUid($uid);
            $startingPage->subpages = $pageTree;
            return $startingPage;
        }
        return $pageTree;
    }
}