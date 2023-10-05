<?php

namespace UBOS\Puck\Domain\Model\Page;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puck\Domain\Model\Page;
use UBOS\Puck\Domain\Model\Person;
use UBOS\Puck\Domain\Repository\PageRepository;

#[ModelPersistence(
    table: "pages",
    parentClass: Page::class,
    recordType: PageRepository::DOKTYPES['person'])]
class PersonPage extends Page
{
    /**
     * @var ObjectStorage<Person>|null
     * @Lazy
     */
    #[ModelColumn("string")]
    public ObjectStorage|null $pagePersons = null;
}