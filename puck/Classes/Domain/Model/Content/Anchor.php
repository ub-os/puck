<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\WizardTab;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("03_menu")
 */
class Anchor extends AbstractEntity
{
    /**
     * @var string
     */
    public string $header;
    /**
     * @var string
     */
    public string $subheader;
    /**
     * @var string
     * @Transient
     */
    protected string $elementId;
    /**
     * @return string
     */
    public function getElementId(): string
    {
        return urlencode(strtolower($this->subheader)).'-c'.$this->uid;
    }
    /**
     * @param string $elementId
     */
    public function setElementId(string $elementId): void
    {
        $this->elementId = $elementId;
    }
}