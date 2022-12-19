<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Stage extends Text
{

    /**
     * @var ?ObjectStorage<FileReference>
     * @Lazy
     */
    public ?ObjectStorage $assets = null;

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
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
        assets,';
    }

    /**
     * @return array
     */
    public function columnsOverrides(): array
    {
        $cropVariants = require(ExtensionManagementUtility::extPath('theme') . 'Configuration/TCA/Common/CropVariants.php');
        return [
            'bodytext' => [
                'config' => [
                    'enableRichtext' => true,
                ],
            ],
            'assets' => [
                'config' => [
                    'overrideChildTca' => [
                        'columns' => [
                            'crop' => [
                                'config' => [
                                    'cropVariants' => [
                                        '2:1' => $cropVariants['2:1'],
                                        '3:2' => $cropVariants['3:2'],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];
    }
}