<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use UBOS\Puck\Attribute\FlexFormProperty;

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