<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use UBOS\Puck\Domain\Repository\ContentRepository;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("02_menu")
 */
class MenuAnchors extends Text
{
    /**
     * @var ?array
     * @Lazy
     * @Transient
     */
    protected ?array $anchors = null;

    /**
     * @return array
     */
    public function getAnchors(): array
    {
        if ($this->anchors === null) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $contentRepository = $objectManager->get(ContentRepository::class);
            $this->anchors = $contentRepository->findContentObjectsBy('Anchor', 'pid', $this->pid)->toArray();
        }
        return $this->anchors;
    }

    /**
     * @param array $anchors
     * @return void
     */
    public function setAnchors(array $anchors): void
    {
        $this->anchors = $anchors;
    }

}