<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;

use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;
use UBOS\Puck\Domain\Model\Trait\Content\ContainerLayout;


/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Text extends AbstractEntity
{
    use SectionHeader;
    use ContainerLayout;

    /**
     * @var string
     */
    public string $bodytext = '';

    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $layout;

    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $frameClass = '';

    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $icon = '';
}