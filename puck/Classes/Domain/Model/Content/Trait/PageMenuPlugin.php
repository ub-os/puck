<?php
namespace UBOS\Puck\Domain\Model\Content\Trait;

use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use UBOS\Puckloader\Attribute\FlexFormProperty;

trait PageMenuPlugin {

    use FlexForms;

    /**
     * @var ?array
     * @Transient
     */
    public ?array $menu = null;

    #[FlexFormProperty('FILE:EXT:puck/Configuration/FlexForms/PageMenu.xml')]
    public string $piFlexform = '';
}