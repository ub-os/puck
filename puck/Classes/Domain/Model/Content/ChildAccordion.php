<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Domain\Model\Content\Trait\ContainerChild;
use UBOS\Puck\Domain\Model\Content\Trait\SectionHeader;
use UBOS\Puck\Domain\Model\Content\Trait\TextMediaLayout;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;


#[ModelPersistence("tt_content")]
#[ContentElementWizard('01_content')]
class ChildAccordion extends AbstractEntity
{
    use SectionHeader;
    use TextMediaLayout;
    use ContainerChild;

    /**
     * @var string
     */
    public string $bodytext = '';

}