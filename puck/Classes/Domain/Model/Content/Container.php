<?php

namespace UBOS\Puck\Domain\Model\Content;

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
use UBOS\Puck\Attribute\ContainerElement;
use UBOS\Puck\Attribute\ContentElementWizard;

/**
 * @DatabaseTable("tt_content")
 */
#[ContentElementWizard("01_content", order:30)]
#[ContainerElement([
    [
        ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_accordion']]
    ]
])]
class Container extends Text
{
}