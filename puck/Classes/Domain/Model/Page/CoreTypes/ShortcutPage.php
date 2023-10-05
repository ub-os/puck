<?php

namespace UBOS\Puck\Domain\Model\Page\CoreTypes;

use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Model\Page;
use UBOS\Puck\Domain\Repository\PageRepository;

#[ModelPersistence(
    table: "pages",
    parentClass: Page::class,
    recordType: PageRepository::DOKTYPES['shortcut'])]
class ShortcutPage extends Page
{
}