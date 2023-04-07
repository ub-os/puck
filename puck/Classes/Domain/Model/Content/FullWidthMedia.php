<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Attribute\ContentElementWizard;


/**
 * @DatabaseTable("tt_content")
 */
#[ContentElementWizard('01_content', order: 3)]
class FullWidthMedia extends Media
{
    public function getContainerWidth(): int
    {
        if (in_array($this->mediaLayout, ['left','right'])) {
            return 12;
        }
        return $this->containerWidth;
    }
    public function getContainerOffset(): int
    {
        if (in_array($this->mediaLayout, ['left','right'])) {
            return 0;
        }
        return $this->containerOffset;
    }
}