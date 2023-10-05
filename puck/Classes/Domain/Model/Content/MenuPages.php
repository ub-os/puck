<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puck\Domain\Model\Content\Trait\PageMenuPlugin;
use UBOS\Puck\Domain\Model\Content\Trait\TextMediaLayout;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puckloader\Attribute\PluginElement;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('03_menu')]
#[PluginElement('PageMenu')]
class MenuPages extends Text
{
    use TextMediaLayout;
    use PageMenuPlugin;


    /**
     * @var string
     */
    #[ModelColumn("text")]
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