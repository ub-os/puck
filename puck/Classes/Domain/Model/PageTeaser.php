<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tx_puck_domain_model_page_teaser")]
class PageTeaser extends AbstractEntity
{
    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $page;

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $title = '';

    /**
     * @var string
     */
    #[ModelColumn("varchar1024")]
    public string $text = '';

    /**
     * @var ObjectStorage<FileReference>|null
     */
    #[ModelColumn("string")]
    public ObjectStorage|null $media = null;

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $icon = '';

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $parentTable;
}