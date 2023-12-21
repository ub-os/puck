<?php

namespace UBOS\Puck\Domain\Model\Page;

use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Model\Page;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Model\Page\Trait\ExternalUrl;

#[ModelPersistence(
    table: "pages",
    parentClass: Page::class,
    recordType: PageRepository::DOKTYPES['teaser'])]
class Teaser extends Page
{
    use ExternalUrl;
}