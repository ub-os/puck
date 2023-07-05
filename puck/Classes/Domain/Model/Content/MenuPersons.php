<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Attribute\PluginElement;
use UBOS\Puck\Domain\Model\Trait\Content\PageMenuPlugin;

/**
 * @DatabaseTable("tt_content")
 */
#[ContentElementWizard('03_menu')]
#[PluginElement('PersonMenu')]
class MenuPersons extends Text
{
    use PageMenuPlugin;
}