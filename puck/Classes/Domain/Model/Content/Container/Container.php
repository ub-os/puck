<?php

namespace UBOS\Puck\Domain\Model\Content\Container;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Puck\Domain\Model\Content\Text;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Core\Utility\DebugUtility;


/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Container extends Text
{
    public const CONTAINER_CONFIGURATION = [
        [
            ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_accordion']]
        ],
    ];

}