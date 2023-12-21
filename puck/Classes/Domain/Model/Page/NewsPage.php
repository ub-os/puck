<?php

namespace UBOS\Puck\Domain\Model\Page;

use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Model\Page;
use UBOS\Puck\Domain\Model\Person;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Model\Page\Trait\ExternalUrl;

#[ModelPersistence(
    table: "pages",
    parentClass: Page::class,
    recordType: PageRepository::DOKTYPES['news'])]
class NewsPage extends Page
{
    use ExternalUrl;
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