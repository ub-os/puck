<?php

namespace UBOS\Puck\Domain\Model\Page;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

use UBOS\Puck\Domain\Model\Person;

/**
 * @DatabaseTable("pages")
 */
class Post extends Page
{
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $postDate = '';

    /**
     * @var ?Person
     * @DatabaseField("int")
     */
    public ?Person $postAuthor = null;
}