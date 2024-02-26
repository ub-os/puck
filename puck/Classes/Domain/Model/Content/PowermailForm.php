<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use UBOS\Puck\Domain\Model\Content\Text;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('99_hidden')]
class PowermailForm extends Text
{
}