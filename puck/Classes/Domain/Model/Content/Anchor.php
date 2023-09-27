<?php

namespace UBOS\Puck\Domain\Model\Content;

use UBOS\Puckloader\Attribute\ModelColumn;

use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('03_menu')]
class Anchor extends AbstractEntity
{
    /**
     * @var string
     */
    public string $header = '';
    /**
     * @var string
     */
    public string $subheader = '';
    /**
     * @var string
     * @Transient
     */
    protected string $elementId = '';
    /**
     * @return string
     */
    public function getElementId(): string
    {
        //return urlencode(strtolower($this->subheader)).'-c'.$this->uid;
        return $this->subheader;
    }
    /**
     * @param string $elementId
     */
    public function setElementId(string $elementId): void
    {
        $this->elementId = $elementId;
    }
}