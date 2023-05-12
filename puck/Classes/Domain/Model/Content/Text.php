<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Domain\Model\Trait\Content\ContainerLayout;
use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;


/**
 * @DatabaseTable("tt_content")
 */
#[ContentElementWizard('01_content', order: 1)]
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
    public string $layout = '';

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