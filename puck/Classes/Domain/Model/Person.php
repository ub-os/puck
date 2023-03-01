<?php

namespace UBOS\Puck\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Domain\Model\Page\PersonPage;

/**
 * @DatabaseTable("tx_puck_domain_model_person")
 */
class Person extends AbstractEntity
{
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $name = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $slug = '';
    /**
     * @var int
     * @DatabaseField(type="int")
     */
    public int $isTeamMember = 0;
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $description = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $position = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $email = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $phone = '';
    /**
     * @var ?ObjectStorage<PersonPage>
     * @DatabaseField(type="int")
     * @Lazy
     */
    public ?ObjectStorage $pages = null;
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $link = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $linkLinkedin = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $linkXing = '';
    /**
     * @var ?ObjectStorage<FileReference>
     * @DatabaseField(type="string")
     */
    public ?ObjectStorage $assets = null;
}