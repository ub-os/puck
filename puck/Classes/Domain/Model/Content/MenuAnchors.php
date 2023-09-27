<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Repository\ContentRepository;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('03_menu')]
class MenuAnchors extends Text
{
    protected ?ContentRepository $contentRepository = null;
    public function injectContentRepository(ContentRepository $contentRepository) : void
    {
        $this->contentRepository = $contentRepository;
    }
    /**
     * @var ?array
     * @Transient
     */
    protected ?array $anchors = null;

    /**
     * @return array
     */
    public function getAnchors(): array
    {
        if ($this->anchors === null) {
            $this->anchors = $this->contentRepository->findContentObjectsBy('Anchor', 'pid', $this->pid)->toArray();
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