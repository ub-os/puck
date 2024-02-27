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
use UBOS\Puckloader\Attribute\ContainerElement;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tt_content")]
#[ContentElementWizard("01_content", order:29)]
#[ContainerElement([
    [
        ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_accordion']]
    ]
])]
class Container extends Text
{
}