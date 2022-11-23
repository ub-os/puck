<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Theme\Domain\Repository\PageRepository;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("02_menu")
 */
class MenuPages extends Text
{

    /**
     * @var string
     */
    protected string $pages;

    /**
     * @var string
     * @DatabaseField("string")
     */
    protected string $parents;

    /**
     * Computed properties.
     */

    /**
     * @var array
     * @Lazy
     * @Transient
     */
    public array $menu = [];

    /**
     * @return void
     */
    public function setMenu(): void
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $pageRepository = $objectManager->get(PageRepository::class);
        $this->menu = $pageRepository->getPagesAndSubPages($this->pages, $this->parents);
    }

    /**
     * @return void
     */
    public function computeProperties(): void
    {
        $this->setMenu();
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
        --palette--;;layout,
        --palette--;;headers,
        --palette--;;menu_pages,';
    }

    /**
     * @return array
     */
    public function columnsOverrides(): array
    {
        return [];
    }
}