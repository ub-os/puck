<?php

namespace UBOS\Puck\Domain\Model\Content\Container;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Domain\Model\Content\Text;

/**
 * @DatabaseTable("tt_content")
 */
#[ContentElementWizard('01_content')]
#[ContainerElement([
    [
        ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_column,puck_child_card']]
    ],
])]
class Row extends Text
{
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
     * @DatabaseField("int")
     */
    public int $flexGrow = 0;

}