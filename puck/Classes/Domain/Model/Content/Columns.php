<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Puck\Domain\Model\InlineMedia;
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
     * @var ?ObjectStorage<InlineMedia>
     * @DatabaseField("string")
     * @Cascade("remove")
     * @Lazy
     */
    public ?ObjectStorage $inlineMedia = null;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $itemColumnWidth = 0;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $columnPosition = '';

    /**
     * @var ?array
     * @Transient
     */
    protected ?array $columnWidths = null;

    /**
     * @return array
     */
    public function getColumnWidths(): array
    {
        if ($this->columnWidths === null) {
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
        return $this->columnWidths;
    }

    /**
     * @param array $columnWidths
     * @return void
     */
    public function setColumnWidths(array $columnWidths): void
    {
        $this->columnWidths = $columnWidths;
    }

}