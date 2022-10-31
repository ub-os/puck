<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Theme\Domain\Model\Page;
use UBOS\Theme\Domain\Repository\PageRepository;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class MenuPages extends AbstractEntity
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
     * @var string
     */
    protected string $pages;

    /**
     * @var string
     */
    protected string $parents;

    public array $computedProps = ['menu'];
    
    /**
     * @return array
     */
    public function getMenu(): array
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $pageRepository = $objectManager->get(PageRepository::class);
        $pages = [];
        foreach(explode(',', $this->pages) as $uid) {
            $pages[] = $pageRepository->findByUid($uid);
        }
        $subpages = [];
        foreach(explode(',', $this->parents) as $uid) {
            foreach($pageRepository->findByPid($uid) as $page) {
                $subpages[] = $page;
            }
        }
        return [
            'pages' => $pages,
            'subpages' => $subpages
        ];
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