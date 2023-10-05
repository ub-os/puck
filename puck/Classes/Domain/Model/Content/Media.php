<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puck\Domain\Model\Content\Trait\TextMediaLayout;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;


#[ModelPersistence("tt_content")]
#[ContentElementWizard('01_content', order: 2)]
class Media extends Text
{

    use TextMediaLayout;
}