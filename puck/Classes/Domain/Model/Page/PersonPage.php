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
class PersonPage extends Page
{
    /**
     * @var ?ObjectStorage<Person>
     * @DatabaseField("string")
     * @Lazy
     */
    public ?ObjectStorage $pagePersons = null;
}