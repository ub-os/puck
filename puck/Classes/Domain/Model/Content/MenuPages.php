<?php

namespace UBOS\Puck\Domain\Model\Content;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puck\Domain\Repository\Page\PageRepository;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("02_menu")
 */
class MenuPages extends Modal
{
    protected ?PageRepository $pageRepository = null;
    public function injectPageRepository(PageRepository $pageRepository) : void
    {
        $this->pageRepository = $pageRepository;
    }
    /**
     * @var string
     */
    public string $pages;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $parents;

    /**
     * @var int
     */
    public int $itemColumnWidth = 0;

    /**
     * @var string
     * @DatabaseField ("string")
     */
    protected string $menuItemConfig = '';

    /**
     * @return array
     */
    public function getMenuItemConfig(): array
    {
        $array = explode(',', $this->menuItemConfig);
        $settings = [];
        foreach($array as $value) {
            $settings[$value] = true;
        }
        return $settings;
    }

    /**
     * @param string $menuItemConfig
     */
    public function setMenuItemConfig(string $menuItemConfig): void
    {
        $this->menuItemConfig = $menuItemConfig;
    }

    /**
     * @var ?array
     * @Transient
     */
    protected ?array $menu = null;

    /**
     * @return array
     */
    public function getMenu(): array
    {
        if ($this->menu === null) {
            $this->menu = $this->pageRepository->findByUidListAndPidList($this->pages, $this->parents, ['navHide' => 1, 'orderByUidList' => 1]);
        }
        return $this->menu;
    }

    /**
     * @param array $menu
     * @return void
     */
    public function setMenu(array $menu): void
    {
        $this->menu = $menu;
    }
}