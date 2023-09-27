<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;


use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Model\Trait\Content\ContainerChild;
use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;
use UBOS\Puck\Domain\Model\Trait\Content\TextMediaLayout;

#[ModelPersistence("tt_content")]
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