<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puckloader\Attribute\PluginElement;
use UBOS\Puck\Domain\Model\Trait\Content\PageMenuPlugin;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('03_menu')]
#[PluginElement('PostMenu')]
class MenuPosts extends Text
{
    use PageMenuPlugin;
}