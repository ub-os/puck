<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Domain\Model\Trait\Content\ContainerChild;
use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;

/**
 * @DatabaseTable("tt_content")
 */
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