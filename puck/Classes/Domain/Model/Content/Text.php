<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Domain\Model\Content\Trait\ContainerLayout;
use UBOS\Puck\Domain\Model\Content\Trait\SectionHeader;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;


#[ModelPersistence("tt_content")]
#[ContentElementWizard('01_content', order: 1)]
class Text extends AbstractEntity
{
    use SectionHeader;
    use ContainerLayout;

    /**
     * @var string
     */
    public string $bodytext = '';

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $layout = '';

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $frameClass = '';

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $icon = '';
}