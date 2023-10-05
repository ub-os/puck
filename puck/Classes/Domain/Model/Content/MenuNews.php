<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puck\Domain\Model\Content\Trait\PageMenuPlugin;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puckloader\Attribute\PluginElement;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('03_menu')]
#[PluginElement('NewsMenu')]
class MenuNews extends Text
{
    use PageMenuPlugin;
}