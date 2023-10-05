<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Domain\Model\Content\Trait\ContainerChild;
use UBOS\Puck\Domain\Model\Content\Trait\SectionHeader;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;


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