<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tx_puck_domain_model_person")]
class Person extends AbstractEntity
{
    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string
     */
    public string $slug = '';

    /**
     * @var int
     */
    public int $isTeamMember = 0;

    /**
     * @var string
     */
    public string $description = '';

    /**
     * @var string
     */
    public string $position = '';

    /**
     * @var string
     */
    public string $email = '';

    /**
     * @var string
     */
    public string $phone = '';

    /**
     * @var ObjectStorage<Page>|null
     * @Lazy
     */
    public ObjectStorage|null $pages = null;

    /**
     * @var string
     */
    public string $link = '';

    /**
     * @var string
     */
    public string $linkLinkedin = '';

    /**
     * @var string
     */
    public string $linkXing = '';

    /**
     * @var ObjectStorage<FileReference>|null
     */
    public ObjectStorage|null $assets = null;
}