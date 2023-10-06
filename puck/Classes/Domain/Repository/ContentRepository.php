<?php

namespace UBOS\Puck\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentRepository extends Repository
{
    public function initializeObject() {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    protected $defaultOrderings = array(
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    );

    public function setContentObjectType($className) {
        $this->objectType = 'UBOS\\Puck\\Domain\\Model\\Content\\'.$className;
    }

    public function findContentObjectsBy(string $className, string $property, mixed $value) {
        $this->setContentObjectType($className);
        $query = $this->createQuery();
        return $query->matching(
            $query->logicalAnd(
                $query->equals($property, $value),
                $query->equals('ctype', 'puck_'.strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)))
                )
        )->execute();
    }
}