<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Theme\UserFunctions\FormEngine\SelectItemsProcFunc;
use UBOS\Theme\Domain\Model\InlineMedia;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;


/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Columns extends Text
{
    /**
     * @var int
     */
    public int $imagecols = 1;

    /**
     * @var ObjectStorage<InlineMedia>
     * @DatabaseField("string")
     * @Cascade("remove")
     * @Lazy
     */
    public ObjectStorage $inlineMedia;

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
        --palette--;;layout,
        --palette--;;headers,
        --palette--;;bodytext,        
    --div--;Items,
        imagecols,
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
            'imagecols' => [
              'config' => [
                  'itemsProcFunc' => SelectItemsProcFunc::class . '->keepItems'
              ]
            ],
            'inline_textmedia' => [
                'label' => 'Columns items',
                'config' => [
                    'overrideChildTca' => [
                        'columns' => [
                            'imageorient' => [
                                'config' => [
                                    'default' => 5,
                                    'itemsProcFunc' => SelectItemsProcFunc::class . '->columnsInlineItemImageorient',
                                ]
                            ]
                        ],
                        'types' => [
                            '1' => [
                                'showitem' => '
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
                                header, 
                                bodytext, 
                            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                                imageorient,
                                assets,
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
                                sys_language_uid, 
                                l10n_parent, 
                                l10n_diffsource, 
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
                                hidden'
                            ],
                        ],
                    ]
                ]
            ]
        ];
    }
}