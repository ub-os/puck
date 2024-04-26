<?php
namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;

use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('01_content', order:31)]
class ErrorText extends Text
{
}