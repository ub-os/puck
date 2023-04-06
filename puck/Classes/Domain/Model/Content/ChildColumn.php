<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;

use UBOS\Puck\Domain\Model\Trait\Content\ContainerChild;
use UBOS\Puck\Domain\Model\Trait\Content\SectionHeader;
use UBOS\Puck\Domain\Model\Trait\Content\TextMediaLayout;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class ChildColumn extends AbstractEntity
{
    use SectionHeader;
    use TextMediaLayout;
    use ContainerChild;

    public int $containerWidth = 0;

    /**
     * @var string
     */
    public string $bodytext = '';


}