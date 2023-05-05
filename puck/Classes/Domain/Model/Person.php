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
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $name = '';
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $slug = '';
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $isTeamMember = 0;
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $description = '';
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $position = '';
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $email = '';
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $phone = '';
    /**
     * @var ObjectStorage<PersonPage>|null
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     * @Lazy
     */
    public ObjectStorage|null $pages = null;
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(1024) DEFAULT '' NOT NULL")
     */
    public string $link = '';
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(1024) DEFAULT '' NOT NULL")
     */
    public string $linkLinkedin = '';
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(1024) DEFAULT '' NOT NULL")
     */
    public string $linkXing = '';
    /**
     * @var ObjectStorage<FileReference>|null
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public ObjectStorage|null $assets = null;
}