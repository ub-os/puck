<?php

namespace UBOS\Puck\Domain\Model\Content;


use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puckloader\Attribute\ContainerElement;

#[ModelPersistence("tt_content")]
#[ContentElementWizard("01_content", order: 32)]
#[ContainerElement([
    [
        ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_hero_slide']]
    ],
])]
class HeroCarousel extends Text
{
}