<?php

namespace UBOS\Puck\Domain\Model;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @DatabaseTable("tx_puck_domain_model_author")
 */
class Author extends AbstractEntity
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
     * @var string
     * @DatabaseField(type="string")
     */
    public string $description = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $email = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $position = '';
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
}