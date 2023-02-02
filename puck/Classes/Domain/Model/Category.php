<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\DatabaseField;

/**
 * @DatabaseTable("sys_category")
 */
class Category extends AbstractEntity
{
    /**
     * @var string
     */
    public string $title = '';
    /**
     * @var ObjectStorage|null
     * @Lazy
     */
    public ?ObjectStorage $parent = null;
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $slug = '';
}