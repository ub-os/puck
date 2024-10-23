<?php

namespace UBOS\Puck;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use B13\Container\Tca\ContainerConfiguration;
use B13\Container\Tca\Registry;
use UBOS\Puckloader\Utility\TcaUtility;

class PageTypeDefinition
{
    public function __construct(
        public int $doktype,
        public string $label = '',
        public string $description = '',
        public string $group = 'default',
        public string $icon = 'apps-pagetree-page',
        public string $showItem = '',
        public array $showItemAdditions = [],
        public array $columnsOverrides = [],
    )
    {
    }

    public function addTCA(): void
    {
        $this->showItem = $this->showItem
            ?: $GLOBALS['TCA']['pages']['types'][$this->doktype]['showitem']
            ?? $GLOBALS['TCA']['pages']['types'][1]['showitem'];
        $GLOBALS['TCA']['pages']['types'][$this->doktype] = [
            'showitem' => $this->showItem,
            'columnsOverrides' => $this->columnsOverrides,
        ];
        foreach ($this->showItemAdditions as $addition) {
            ExtensionManagementUtility::addToAllTCAtypes(
                'pages',
                $addition[0] ?? '',
                '*,'. $this->doktype,
                $addition[1] ?? 'after:--palette--;;title'
            );
        }
        if (!$this->label) return;
        ExtensionManagementUtility::addTcaSelectItem(
            'pages',
            'doktype',
            TcaUtility::selectItemHelper([
                $this->label,
                $this->doktype,
                $this->icon,
                $this->group
            ]),
            '1',
            'after'
        );
        $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$this->doktype] = $this->icon;
        $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$this->doktype . '-hideinmenu'] = $this->icon. '_hideinmenu';
        $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$this->doktype . '-root'] = 'apps-pagetree-page-domain';

    }

    public function addTypoScript(): void
    {
    }

}