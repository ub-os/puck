<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\PluginElement;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('03_menu')]
#[PluginElement('AnchorMenu')]
class MenuAnchors extends Text
{
    /**
     * @var ?array
     * @Transient
     */
    protected ?array $anchors = null;

    /**
     * @return array
     */
    public function getAnchors(): array
    {
        return $this->anchors;
    }
    /**
     * @param array $anchors
     * @return void
     */
    public function setAnchors(array $anchors): void
    {
        $this->anchors = $anchors;
    }
}