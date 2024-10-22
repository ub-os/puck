<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UBOS\Puck\Domain\Model\Content\Text;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tt_content")]
class PowermailForm extends AbstractEntity
{
}