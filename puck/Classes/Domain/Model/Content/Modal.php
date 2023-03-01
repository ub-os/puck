<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Modal extends Media
{
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $cardMediaSize = '';

}