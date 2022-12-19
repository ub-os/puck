<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Theme\Domain\Model\InlineMedia;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Accordions extends Text
{
    /**
     * @var ?ObjectStorage<InlineMedia>
     * @DatabaseField("string")
     * @Cascade("remove")
     * @Lazy
     */
    public ?ObjectStorage $inlineMedia = null;

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
        --palette--;;gridContainer,
        --palette--;;appearance,
        --palette--;;headers,
        --palette--;;bodytext,        
    --div--;Items,
        inline_media,';
    }

    /**
     * @return array
     */
    public function columnsOverrides(): array
    {
        return [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                ]
            ],
            'inline_media' => [
                'label' => 'Accordion items',
                'config' => [
                    'overrideChildTca' => [
                        'types' => [
                            '1' => $GLOBALS['TCA']['tx_theme_domain_model_inline_media']['types']['accordions'],
                        ],
                    ]
                ]
            ]
        ];
    }
}