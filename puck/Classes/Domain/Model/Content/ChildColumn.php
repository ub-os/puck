<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Domain\Model\Trait\Content\ContainerChild;
use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;
use UBOS\Puck\Domain\Model\Trait\Content\TextMediaLayout;

/**
 * @DatabaseTable("tt_content")
 */
#[ContentElementWizard('01_content')]
class ChildColumn extends AbstractEntity
{
    use SectionHeader;
    use TextMediaLayout;
    use ContainerChild;

    public int $containerWidth = 0;

    /**
     * @var string
     */
    public string $bodytext = '';


}