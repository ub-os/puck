<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;


/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
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