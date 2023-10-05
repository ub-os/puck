<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("sys_category")]
class Category extends AbstractEntity
{
    /**
     * @var string
     */
    public string $title = '';
    /**
     * @var Category|null
     */
    public ?Category $parent = null;
    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $slug = '';
}