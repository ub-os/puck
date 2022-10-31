<?php

namespace UBOS\Theme\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class PageRepository extends Repository
{
    public function findByPids($pids) {
        $uidArray = explode(",", $pids);
        $query = $this->createQuery();
        $constraints = [];
        foreach ($uidArray as $key => $value) {
            $constraints[] = $query->equals('pid', $value);
        }
        return $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $constraints
                ),
                $query->equals('hidden', 0),
                $query->equals('deleted', 0)
            )
        )->execute();
    }
    public function findByUids($uids) {
        $uidArray = explode(",", $uids);
        $query = $this->createQuery();
        $constraints = [];
        foreach ($uidArray as $key => $value) {
            $constraints[] = $query->equals('uid', $value);
        }
        return $query->matching(
            $query->logicalAnd(
                $query->logicalOr(
                    $constraints
                ),
                $query->equals('hidden', 0),
                $query->equals('deleted', 0)
            )
        )->execute();
    }
}