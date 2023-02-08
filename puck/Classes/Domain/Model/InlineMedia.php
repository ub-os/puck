<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\DatabaseField;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;

use UBOS\Puck\Domain\Model\Trait\Content\TextMediaLayout;

/**
 * @DatabaseTable("tx_puck_domain_model_inline_media")
 */
class InlineMedia extends AbstractEntity
{
    use TextMediaLayout;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $parentUid;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $parentTable;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $itemType = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $header = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $subheader = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $bodytext = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $layout = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $frameClass = '';

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $icon = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $columnWidth = 0;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $cardMediaSize = '';
}