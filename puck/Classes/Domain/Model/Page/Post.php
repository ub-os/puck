<?php

namespace UBOS\Puck\Domain\Model\Page;

use UBOS\Puckloader\Attribute\ModelColumn;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

use UBOS\Puck\Domain\Model\Person;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("pages")]
class Post extends Page
{
    /**
     * @var string
     */
    #[ModelColumn("datetime")]
    public string $postDate = '';

    /**
     * @var ?Person
     */
    #[ModelColumn("string")]
    public ?Person $postAuthor = null;
}