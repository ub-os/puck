<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

use UBOS\Puck\Annotation\PluginElement;
use UBOS\Puck\Domain\Model\Trait\Content\FlexForm;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("03_menu")
 * @PluginElement(pluginName="PersonMenu",piFlexFormValue="FILE:EXT:puck/Configuration/FlexForms/PageMenu.xml")
 */
class MenuPersons extends Text
{
    use FlexForm;

    /**
     * @var ?array
     * @Transient
     *
     */
    public ?array $menu = null;

}