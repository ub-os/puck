<?php

namespace UBOS\Puck\Domain\Model;

use UBOS\Puckloader\Attribute\ModelColumn;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Domain\Model\Page\PersonPage;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tx_puck_domain_model_person")]
class Person extends AbstractEntity
{
    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $name = '';
    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $slug = '';
    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $isTeamMember = 0;
    /**
     * @var string
     */
    #[ModelColumn("text")]
    public string $description = '';
    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $position = '';
    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $email = '';
    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $phone = '';
    /**
     * @var ObjectStorage<PersonPage>|null
     * @Lazy
     */
    #[ModelColumn("string")]
    public ObjectStorage|null $pages = null;
    /**
     * @var string
     */
    #[ModelColumn("varchar1024")]
    public string $link = '';
    /**
     * @var string
     */
    #[ModelColumn("varchar1024")]
    public string $linkLinkedin = '';
    /**
     * @var string
     */
    #[ModelColumn("varchar1024")]
    public string $linkXing = '';
    /**
     * @var ObjectStorage<FileReference>|null
     */
    #[ModelColumn("string")]
    public ObjectStorage|null $assets = null;
}