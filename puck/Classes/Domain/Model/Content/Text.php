<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;


use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Model\Trait\Content\ContainerLayout;
use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;


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