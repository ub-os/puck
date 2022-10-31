<?php

namespace UBOS\Theme\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\DatabaseField;


/**
 * @DatabaseTable("tx_theme_domain_model_inline_media")
 */
class InlineMedia extends AbstractEntity
{

    public function __construct() {
        $this->assets = new ObjectStorage();
    }
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
     * @var int
     * @DatabaseField("int")
     */
    public int $imageorient = 0;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $imagecols = 0;

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
     * @var ObjectStorage<FileReference>
     * @DatabaseField("string")
     */
    public $assets = null;

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }
}