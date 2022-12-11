<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Theme\UserFunctions\FormEngine\ContentItemsProcFunc;
use UBOS\Theme\Domain\Model\Page;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Media extends Text
{

    /**
     * @var int
     */
    public int $imageorient;

    /**
     * @var int
     */
    public int $imagecols;

    /**
     * @var ObjectStorage<FileReference>
     * @Lazy
     */
    public ObjectStorage $assets;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $bodytext2;

    /**
     * @var ObjectStorage<Page>
     * @Lazy
     */
    public ObjectStorage $pages;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $contentType;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $itemColumnWidth = 6;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $columnPosition = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $textColumnWidth = 6;
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $mediaColumnWidth = 6;

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
        --palette--;;headers,
        --palette--;;bodytext,
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
        --palette--;;gridMedia,
        assets,
        pages,
        bodytext2,
        content_type,';
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
                ],
            ],
            'assets' => [
                'displayCond' => 'FIELD:content_type:=:assets'
            ],
            'pages' => [
                'label' => 'Pages',
                'displayCond' => 'FIELD:content_type:=:page'
            ],
            'bodytext2' => [
                'label' => 'iFrame HTML',
                'config' => [
                    'renderType' => 't3editor',
                    'enableRichtext' => false,
                ],
                'displayCond' => 'FIELD:content_type:=:html'
            ],
            'imageorient' => [
                'onChange' => 'reload',
                'config' => [
                ]
            ],
            'content_type' => [
                'onChange' => 'reload',
                'config' => [
                    'itemsProcFunc' => ContentItemsProcFunc::class.'->keepItems',
                ]
            ],
            'item_column_width' => [
                'label' => 'Default media item width',
                'displayCond' => 'FIELD:content_type:=:assets',
            ],
            'column_position' => [
                'displayCond' => 'FIELD:content_type:=:assets',

            ],
            'media_column_width' => [
            ],
            'text_column_width' => [
            ],
        ];
    }
}