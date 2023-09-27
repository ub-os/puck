<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Model\Trait\Content\ContainerChild;
use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('01_content')]
class ChildHeroSlide extends AbstractEntity
{
    use SectionHeader;
    use ContainerChild;

    /**
     * @var string
     */
    public string $bodytext = '';

    /**
     * @var ObjectStorage<FileReference>|null
     * @Lazy
     */
    public ObjectStorage|null $assets = null;

}