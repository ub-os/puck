<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Puck\Domain\Model\InlineMedia;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Accordions extends Text
{
    /**
     * @var ?ObjectStorage<InlineMedia>
     * @DatabaseField("string")
     * @Cascade("remove")
     * @Lazy
     */
    public ?ObjectStorage $inlineMedia = null;

}