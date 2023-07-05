<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Attribute\ContentElementWizard;
use UBOS\Puck\Attribute\PluginElement;
use UBOS\Puck\Domain\Model\Trait\Content\PageMenuPlugin;
use UBOS\Puck\Domain\Model\Trait\Content\TextMediaLayout;

/**
 * @DatabaseTable("tt_content")
 */
#[ContentElementWizard('03_menu')]
#[PluginElement('PageMenu')]
class MenuPages extends Text
{
    use TextMediaLayout;
    use PageMenuPlugin;


    /**
     * @var string
     * @DatabaseField ("string")
     */
    protected string $menuItemConfig = '';

    /**
     * @return array
     */
    public function getMenuItemConfig(): array
    {
        $array = explode(',', $this->menuItemConfig);
        $settings = [];
        foreach($array as $value) {
            $settings[$value] = true;
        }
        return $settings;
    }

    /**
     * @param string $menuItemConfig
     */
    public function setMenuItemConfig(string $menuItemConfig): void
    {
        $this->menuItemConfig = $menuItemConfig;
    }
}