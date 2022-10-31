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
use UBOS\Theme\Domain\Model\Page;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class TextMedia2 extends AbstractEntity
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
     * @var int
     */
    public int $imageorient;

    /**
     * @var string
     */
    public string $bodytext;

    /**
     * @var ObjectStorage<FileReference>
     */
    public ObjectStorage $assets;

    /**
     * @var string
     */
    public string $bodytext2;

    /**
     * @var ObjectStorage<Page>
     */
    public ObjectStorage $pages;

    /**
     * @var string
     */
    public string $contentType;

    /**
     * @return string
     */
    public function getComputedProp(): string
    {
        return 'lole le';
    }

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
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
        --palette--;;media_config,
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
                    'itemsProcFunc' => SelectItemsProcFunc::class.'->keepItems',
                ]
            ],
            'content_type' => [
                'onChange' => 'reload',
                'config' => [
                    'itemsProcFunc' => SelectItemsProcFunc::class.'->keepItems',
                ]
            ],
            'imagecols' => [
                'displayCond' => [
                    'AND' => [
                        'FIELD:content_type:=:assets',
                        'FIELD:imageorient:>:4',
                    ],
                ],
            ],
            'layout' => [
                'displayCond' => [
                    'AND' => [
                        'FIELD:content_type:=:assets',
                        'FIELD:imageorient:<=:4',
                    ],
                ],
                'config' => [
                    'itemsProcFunc' => SelectItemsProcFunc::class.'->keepItems',
                    'default' => 'cols5-5'
                ]
            ],
        ];
    }
}