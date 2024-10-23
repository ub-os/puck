<?php

namespace UBOS\Puck\Menu;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;
use UBOS\Puck\Domain\Model\Category;
use UBOS\Puck\Domain\Repository\CategoryRepository;
use UBOS\Puck\Menu\Dto\MenuDemand;
use UBOS\Puck\Menu\Dto\CategoryFilter;
use UBOS\Puck\Menu\Dto\CategoryFilterItem;
use UBOS\Puck\Menu\Dto\FetchLinkOptions;
use UBOS\Puck\PageTitle\PuckTitleProvider;

class CategoryFilterBuilder
{
    protected array $settings = [
        'active' => true,
        'categories' => '',
        'treeCategories' => '',
        'multiSelect' => true,
        // levels deep tree is built below a parent category
        'buildTree' => 1,
        // levels deep items are disabled, negative values invert selection
        'disabledTree' => 1,
        // levels deep tree is multiselectable, negative values invert selection
        'multiSelectTree' => 1,
        // don't build tree below inactive category
        'buildTreeBelowEnabledInactive' => false,
        // check if there are any results for a category
        'checkPotential' => false,
        // other arguments to remove when building a filter uri
        'unsetArguments' => ['page', 'recordUid'],
        'demandCategoriesKey' => '0',
        'categoryOrder' => ['sorting' => QueryInterface::ORDER_ASCENDING],
    ];


    protected string $activeCategories = '';
    public function __construct(
        protected Request $request,
        protected UriBuilder $uriBuilder,
        protected CategoryRepository $categoryRepository,
        protected string $menuActionName,
        protected ?MenuDemandRepository $menuRepository = null,
        protected ?MenuDemand $menuDemand = null,
        protected int $contentRecordUid = 0,
        protected int $fetchLinkPageType = 0,
    ) {
    }

    public function configure(array $settings): self
    {
        $this->settings = array_merge($this->settings, $settings);
        $this->activeCategories =
            $this->request->hasArgument('demand')
                ? $this->request->getArgument('demand')['categories'][$this->settings['demandCategoriesKey']]['uids'] ?? ''
                : '';
        return $this;
    }
    
    
    protected function buildUri(array $arguments, bool $isFetchUri = false): string
    {
        if ($isFetchUri && $this->contentRecordUid) {
            $arguments['recordUid'] = $this->contentRecordUid;
        }
        return $this->uriBuilder
            // todo: check if reset() is necessary
            ->reset()
            ->setCreateAbsoluteUri(!$isFetchUri)
            ->setTargetPageType($isFetchUri ? $this->fetchLinkPageType : 0)
            ->setTargetPageUid($this->request->getAttribute('routing')->getPageId())
            ->uriFor($this->menuActionName, $arguments);
    }

    public function buildTree(
        Category $category,
        int $buildTree,
        int $disabledTree,
        int $multiSelectTree,
        array $activeSiblings = [],
        string $enabledParent = '',
    ) : CategoryFilterItem
    {
        $disabled = $disabledTree > 0;
        $multiSelect = $multiSelectTree > 0;
        $isActive = $this->activeCategories && GeneralUtility::inList($this->activeCategories, (string)$category->getUid());
        if (!$buildTree || (!$this->settings['buildTreeBelowEnabledInactive'] && !$disabled && !$isActive)) {
            return $this->buildFilterItem(
                $category,
                $disabled,
                $multiSelect,
                [],
                $activeSiblings,
                $enabledParent
            );
        }

        $activeChildren = [];
        $subCategories = $this->categoryRepository->findByParent($category->getUid());
        foreach($subCategories->toArray() as $subCategory) {
            if (GeneralUtility::inList($this->activeCategories, (string)$subCategory->getUid())) {
                $activeChildren[] = $subCategory->getUid();
            }
        }

        $item = $this->buildFilterItem(
            $category,
            $disabled,
            $multiSelect,
            $activeChildren,
            $activeSiblings,
            $enabledParent
        );

        foreach($subCategories->toArray() as $subCategory) {
            $item->items[] = $this->buildTree(
                $subCategory,
                $buildTree--,
                $this->getTreeIteratorAdvancement($disabledTree),
                $this->getTreeIteratorAdvancement($multiSelectTree),
                $activeChildren,
                enabledParent: !$disabled ? (string)$category->getUid() : '',
            );
        }
        return $item;
    }

    public function buildFilterItem(
        Category $category,
        bool $disabled = false,
        bool $multiSelect = false,
        array $activeChildren = [],
        array $activeSiblings = [],
        string $enabledParent = '',
    ): CategoryFilterItem
    {
        $arguments = $this->request->getArguments();
        $uid = (string)$category->getUid();
        foreach($this->settings['unsetArguments'] as $unsetArgument) {
            unset($arguments[$unsetArgument]);
        }

        $isActive = $this->activeCategories && GeneralUtility::inList($this->activeCategories, $uid);

        if ($activeChildren) {
            $closeArguments = $arguments;
            $closeList = implode(',', array_diff(explode(',', $this->activeCategories), $activeChildren));
            if ($closeList) {
                $closeArguments['demand']['categories'][$this->settings['demandCategoriesKey']]['uids'] = $closeList;
            } else {
                unset($closeArguments['demand']['categories'][$this->settings['demandCategoriesKey']]['uids']);
            }
            $closeUrl = $this->buildUri($closeArguments);
            $closeItem = new CategoryFilterItem(
                label: (string)count($activeChildren),
                url: $closeUrl,
                fetchLinkOptions: new FetchLinkOptions(
                    url: ($this->contentRecordUid && $this->fetchLinkPageType) ? $this->buildUri($closeArguments, true) : $closeUrl,
                    contentId: 'c' . $this->contentRecordUid,
                ),
            );
        }

        if ($disabled) {
            return new CategoryFilterItem(
                label: $category->title,
                active: $isActive,
                activeChildren: $activeChildren,
                closeItem: $closeItem ?? null,
            );
        }

        if ($isActive) {
            if ($this->activeCategories == (string)$category->getUid() || !$this->settings['multiSelect']) {
                $newList = $enabledParent;
            } else {
                $newList = implode(',', array_diff(explode(',', $this->activeCategories), [$uid]));
            }
        } else {
            if (!$this->settings['multiSelect']) {
                $newList = $uid;
            } else if ($multiSelect) {
                $newList = $this->activeCategories ? $this->activeCategories . ',' . $uid : $uid;
            } else {
                $newList = implode(',', array_diff(explode(',', $this->activeCategories), $activeSiblings)) . ',' . $uid;
            }
        }

        if ($newList) {
            $arguments['demand']['categories'][$this->settings['demandCategoriesKey']]['uids'] = $newList;
        } else {
            unset($arguments['demand']['categories'][$this->settings['demandCategoriesKey']]['uids']);
        }

        if ($this->settings['checkPotential'] && $this->menuRepository && $this->menuDemand) {
            $potentialDemand = $this->menuDemand;
            $potentialDemand->categories[$this->settings['demandCategoriesKey']]['uids'] = $newList;
            $potentialDemand->limit = 1;
            $hasNoPotential = !$this->menuRepository->findByMenuDemand($potentialDemand);
        }

        $url = $this->buildUri($arguments);
        return new CategoryFilterItem(
            label: $category->title,
            url: $url,
            fetchLinkOptions: new FetchLinkOptions(
                url: ($this->contentRecordUid && $this->fetchLinkPageType) ? $this->buildUri($arguments, true) : $url,
                contentId: 'c' . $this->contentRecordUid,
            ),
            active: $isActive,
            hasNoPotential: $hasNoPotential ?? false,
            activeChildren: $activeChildren,
            closeItem: $closeItem ?? null,
        );
    }


    protected function getTreeIteratorAdvancement(int $level): int
    {
        if ($level > 1) {
            $level--;
        } else if ($level == 1) {
            $level -= 2;
        } else if ($level < -1) {
            $level++;
        } else if ($level == -1) {
            $level += 2;
        }
        return $level;
    }
    
    public function build(): ?CategoryFilter
    {
        if (!$this->settings['active'] || (!$this->settings['categories'] && !$this->settings['treeCategories'])) {
            return null;
        }
        $filter = new CategoryFilter(
            buildTree: (int)$this->settings['buildTree'],
        );

        $arguments = $this->request->getArguments();
        foreach($this->settings['unsetArguments'] as $unsetArgument) {
            unset($arguments[$unsetArgument]);
        }

        $this->categoryRepository->setDefaultOrderings($this->settings['categoryOrder']);

        if ($this->settings['categories']) {
            $categories = $this->categoryRepository->findByUidList($this->settings['categories'])->toArray();
            foreach($categories as $category) {
                $filter->items[] = $this->buildFilterItem($category);
            }
        }

        if ($this->settings['treeCategories']) {
            $treeCategories = $this->categoryRepository->findByUidList($this->settings['treeCategories'])->toArray();
            foreach($treeCategories as $category) {
                $filter->items[] = $this->buildTree(
                    $category,
                    (int)$this->settings['buildTree'],
                    (int)$this->settings['disabledTree'],
                    (int)$this->settings['multiSelectTree']
                );
            }
        }

        return $filter;
    }
    
    public function addCategorySuffixToPageTitle(): self
    {
        if (!$this->activeCategories) {
            return $this;
        }
        $query =  $this->categoryRepository->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $categories = $query
            ->matching($query->in('uid', explode(',',$this->activeCategories)))
            ->execute()->toArray();
        $pageTitleSuffixCategory =
            PuckTitleProvider::TITLE_DIVIDER .
            implode(', ', array_map(function(Category $category) {
                return $category->title;
            }, $categories));
        $titleProvider = GeneralUtility::makeInstance(PuckTitleProvider::class);
        $titleProvider->setRequest($this->request);
        $titleProvider->setTitle($titleProvider->getTitle() . $pageTitleSuffixCategory);
        return $this;
    }

}