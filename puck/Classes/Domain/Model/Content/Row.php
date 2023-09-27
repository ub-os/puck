<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;


use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puckloader\Attribute\ContainerElement;
use UBOS\Puckloader\Attribute\FlexFormProperty;
use UBOS\Puck\Domain\Model\Trait\Content\FlexForms;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('01_content', order:31)]
#[ContainerElement([
    [
        ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_column, puck_child_card']]
    ]
])]
class Row extends Text
{
    use FlexForms;
    /**
     * @var int
     */
    public int $itemColumnWidth = 0;
    /**
     * @var string
     */
    public string $rowJustify = '';
    /**
     * @var string
     */
    public string $rowAlign = '';
    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $flexGrow = 0;

    /**
     * @var string
     */
    #[ModelColumn("mediumtext")]
    #[FlexFormProperty('FILE:EXT:puck/Configuration/FlexForms/CarouselOptions.xml')]
    public string $options = '';
}