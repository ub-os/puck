<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puck\Domain\Model\Content\Trait\TextMediaLayout;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;


#[ModelPersistence("tt_content")]
#[ContentElementWizard('01_content')]
class Modal extends Text
{
    use TextMediaLayout;

}