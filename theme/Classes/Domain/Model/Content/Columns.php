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
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Core\Utility\DebugUtility;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Columns extends Text
{
    /**
     * @var int
     */
    public int $imagecols = 2;

    /**
     * @var ObjectStorage<InlineMedia>
     * @DatabaseField("string")
     * @Cascade("remove")
     * @Lazy
     */
    public ObjectStorage $inlineMedia;

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
     * @var array
     * @Transient
     */
    public array $columnWidths = [];

    public function setColumnWidths(): void
    {;
        $items = $this->inlineMedia->toArray();
        $itemsWithWidth = array_filter($items, function($item) {
            return $item->columnWidth > 0;
        });
        end($itemsWithWidth);
        $lastKey = key($itemsWithWidth);
        $widths = [];
        foreach ($items as $index=>$item) {
            if ($index <= $lastKey) {
                if ($item->columnWidth > 0) {
                    $widths[] = $item->columnWidth;
                } else {
                    $widths[] = $this->itemColumnWidth;
                }
            } else {
                $widths[] = $widths[$index % ($lastKey+1)];
            }
        }
        $this->columnWidths = $widths;
    }

    public function computeProperties() {
        $this->setColumnWidths();
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
        --palette--;;gridContainer,
        --palette--;;headers,
        --palette--;;bodytext,        
    --div--;Items,
        --palette--;;gridColumns,
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
                'label' => 'Columns items',
                'config' => [
                    'overrideChildTca' => [
                        'columns' => [
                            'imageorient' => [
                            ]
                        ],
                        'types' => [
                            '1' => [
                                'showitem' => '
                            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                                column_width,
                                header, 
                                bodytext, 
                            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                                --palette--;;gridMedia,
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