<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\WizardTab;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("02_menu")
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

    /**
     * Content Element TCA
     */

    /**
     * @return string
     */
    public function showItem(): string
    {
        return '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        header, subheader,';
    }

    /**
     * @return array
     */
    public function columnsOverrides(): array
    {
        return [
            'header' => [
                'label' => 'Title'
            ],
            'subheader' => [
                'label' => '#',
                'config' => [
                    'enableRichtext' => true,
                ]
            ]
        ];
    }
}