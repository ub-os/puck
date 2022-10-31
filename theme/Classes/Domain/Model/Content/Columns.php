<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Theme\UserFunctions\FormEngine\SelectItemsProcFunc;
use UBOS\Theme\Domain\Model\InlineMedia;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Columns extends AbstractEntity
{
    /**
     * @var string
     */
    public string $header;

    /**
     * @var string
     */
    public string $headerLayout;

    /**
     * @var string
     */
    public string $headerPosition;

    /**
     * @var string
     */
    public string $subheader;

    /**
     * @var string
     */
    public string $layout;

    /**
     * @var string
     */
    public string $bodytext;

    /**
     * @var ObjectStorage<InlineMedia>
     * @DatabaseField("string")
     */
    public ObjectStorage $inlineMedia;

    /**
     * @return string
     */
    public function showItem(): string
    {
        return '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;frames,
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
            'inline_textmedia' => [
                'label' => 'Columns items',
                'config' => [
                    'overrideChildTca' => [
                        'columns' => [
                            'imageorient' => [
                                'config' => [
                                    'default' => 5,
                                    'itemsProcFunc' => UBOS\Theme\UserFunctions\FormEngine\SelectItemsProcFunc::class . '->columnsInlineItemImageorient',
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