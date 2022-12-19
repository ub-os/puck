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

    public function findPagesByPid(int $pid, $options = [])
    {
        $options = array_merge(
            [
                'navHide' => 0,
            ],
            $options
        );
        $query = $this->createQuery();
        $constraints = array(
            $query->equals('pid', $pid),
            $query->lessThan('doktype', 100),
        );
        if ($options['navHide']) {
            $constraints[] = $query->equals('nav_hide', 0);
        }
        return $query->matching(
            $query->logicalAnd(
                $constraints
            )
        )->execute()->toArray();
    }

    public function findByUidListAndPidList($uidList, $pidList, $options = [])
    {
        $options = array_merge(
            [
                'navHide' => 0,
                'orderByUidList' => 0,
            ],
            $options
        );
        $query = $this->createQuery();
        $uidArray = explode(',', $uidList);
        $pidUidConstraints = [];
        foreach ($uidArray as $key => $value) {
            $pidUidConstraints[] = $query->equals('uid', $value);
        }
        foreach (explode(',', $pidList) as $key => $value) {
            $pidUidConstraints[] = $query->equals('pid', $value);
        }
        $constraints = array(
            $query->logicalOr(
                $pidUidConstraints
            ),
            $query->lessThan('doktype', 100),
        );
        if ($options['navHide']) {
            $constraints[] = $query->equals('nav_hide', 0);
        }
        $queryResult = $query->matching(
            $query->logicalAnd(
                $constraints
            )
        )->execute();
        $result = $queryResult->toArray();
        if ($options['orderByUidList']) {
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