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
use UBOS\Theme\UserFunctions\FormEngine\ContentItemsProcFunc;


/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class FullWidthMedia extends Media
{

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
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
        imageorient,
        assets,';
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
            'imageorient' => [
                'config' => [
                    'itemsProcFunc' => ContentItemsProcFunc::class . '->keepItems',
                ]
            ],
            'container_width' => [
                'displayCond' => 'FIELD:imageorient:>:4',
            ],
            'container_offset' => [
                'displayCond' => 'FIELD:imageorient:>:4',
            ],
            'container_position' => [
                'displayCond' => 'FIELD:imageorient:>:4',
            ]
        ];
    }
}