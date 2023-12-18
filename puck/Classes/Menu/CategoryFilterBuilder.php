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
        'categories' => '',
        'groupCategories' => '',
        'active' => true,
        'multiSelect' => false,
        'multiSelectWithinGroup' => false,
        'checkPotential' => false,
        'unsetArguments' => ['page', 'object'],
        'categoryDemandKey' => 'categories',
        'groupDepth' => 1,
        'categoryOrder' => ['sorting' => QueryInterface::ORDER_ASCENDING],
        'buildSecondLevelOfInactiveParent' => false,
        'elements' => [
            [
                'uid' => '',
                'category' => null,
                'multiSelect' => false,
                'disabled' => true,
                'tree' => [
                    [
                        'multiSelect' => true,
                        'disabled' => false,
                    ]
                ],
                'treeDepth' => 2,
            ],
            []
        ]
    ];


    protected array $filterElementConfiguration = [
        'uid' => '',
        'category' => null,
        'multiSelect' => false,
        'buildTreeDepth' => 1,
        'disabledTreeDepth' => 0,
        'buildTreeDepthBelowDisabled' => 0,
        'siblings' => ''
    ];

    protected array $filterConfiguration = [
        'elementConfigurations' => [],
    ];

    protected string $activeCategories = '';
    public function __construct(
        protected Request $request,
        protected UriBuilder $uriBuilder,
        protected CategoryRepository $categoryRepository,
        protected string $menuActionName,
        protected ?MenuDemandRepository $menuRepository = null,
        protected ?MenuDemand $menuDemand = null,
        protected int $menuContentObjectUid = 0,
        protected int $fetchLinkPageType = 0,
    ) {
    }

    public function configure(array $settings): self
    {
        $this->settings = array_merge($this->settings, $settings);
        $this->activeCategories = $this->request->hasArgument('demand') ? $this->request->getArgument('demand')[$this->settings['categoryDemandKey']] ?? '' : '';
        return $this;
    }
    
    
    protected function buildUri(array $arguments, bool $isFetchUri = false): string
    {
        if ($isFetchUri && $this->menuContentObjectUid) {
            $arguments['object'] = $this->menuContentObjectUid;
        }
        return $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(!$isFetchUri)
            ->setTargetPageType($isFetchUri ? $this->fetchLinkPageType : 0)
            ->uriFor($this->menuActionName, $arguments);
    }

    public function build2(): ?CategoryFilter
    {
        if (!$this->settings['elements']) {
            return null;
        }
        $filter = new CategoryFilter();
        $activeCategoryArray = $this->activeCategories ? explode(',', $this->activeCategories) : [];
        $arguments = $this->request->getArguments();
        foreach($this->settings['unsetArguments'] as $unsetArgument) {
            unset($arguments[$unsetArgument]);
        }
        $this->categoryRepository->setDefaultOrderings($this->settings['categoryOrder']);
        return $filter;
    }
    
    public function build(): ?CategoryFilter
    {
        if (!$this->settings['active'] || (!$this->settings['categories'] && !$this->settings['groupCategories'])) {
            return null;
        }
        $filter = new CategoryFilter(
            groupDepth: $this->settings['groupDepth'],
        );
        $currentCategories = $this->activeCategories ? explode(',', $this->activeCategories) : [];

        $arguments = $this->request->getArguments();
        foreach($this->settings['unsetArguments'] as $unsetArgument) {
            unset($arguments[$unsetArgument]);
        }

        $this->categoryRepository->setDefaultOrderings($this->settings['categoryOrder']);

        if ($this->settings['categories']) {
            $categories = $this->categoryRepository->findByUidList($this->settings['categories'])->toArray();
            foreach($categories as $category) {
                $filter->items[] = $this->buildItem($category);
            }
        }

        if ($this->settings['groupCategories']) {
            $groupFilterCategories = $this->categoryRepository->findByUidList($this->settings['groupCategories'])->toArray();
            foreach($groupFilterCategories as $category) {
                $groupItem = new CategoryFilterItem(
                    label: $category->title,
                );

                $subCategories = $this->categoryRepository->findByParent($category->getUid());
                foreach($subCategories->toArray() as $subCategory) {
                    if (GeneralUtility::inList($this->activeCategories, (string)$subCategory->getUid())) {
                        $groupItem->activeItemsUids[] = $subCategory->getUid();
                    }
                }
                foreach($subCategories->toArray() as $subCategory) {
                    $newActiveUids = $currentCategories;
                    // if !multiselectWithinGroup and multiselect and group has actives
                    // => goal: remove other actives from current group, but not from other groups
                    if (!($this->settings['multiSelectWithinGroup'] ?? false) && $this->settings['multiSelect'] && $groupItem->activeItemsUids) {
                        $newActiveUids = array_diff($currentCategories, array_diff($groupItem->activeItemsUids, [$subCategory->getUid()]));
                    }

                    $item = $this->buildItem(
                        $subCategory,
                        implode(',',$newActiveUids),
                    );

                    if ($this->settings['groupDepth'] === 2) {
                        $subCategories2 = $this->categoryRepository->findByParent($subCategory->getUid());
                        $item->activeItemsUids[] = $subCategory->getUid();
                        foreach($subCategories2->toArray() as $subCategory2) {
                            if (GeneralUtility::inList($this->activeCategories, (string)$subCategory2->getUid())) {
                                $item->active = true;
                                $item->activeItemsUids[] = $subCategory2->getUid();
                                $groupItem->activeItemsUids[] = $subCategory2->getUid();

                            }
                        }
                        if ($this->settings['buildSecondLevelOfInactiveParent'] || $item->active) {
                            foreach($subCategories2->toArray() as $subCategory2) {
                                $item->items[] = $this->buildItem(
                                    $subCategory2,
                                    '',
                                    (string)$subCategory->getUid());
                            }
                        }

                        if ($item->active) {
                            $closeArguments = $arguments;
                            if (!is_array($closeArguments['demand'])) {
                                $closeArguments['demand'] = [];
                            }
                            $newDemandCategory = implode(',', array_diff($currentCategories, $item->activeItemsUids)) ?: null;
                            if ($newDemandCategory) {
                                $closeArguments['demand'][$this->settings['categoryDemandKey']] = $newDemandCategory;
                            } else {
                                unset($closeArguments['demand'][$this->settings['categoryDemandKey']]);
                            }
                            $item->closeItem = new CategoryFilterItem(
                                label: $item->label,
                                url: $this->buildUri($closeArguments),
                                fetchLinkOptions: new FetchLinkOptions(
                                    url: $this->buildUri($closeArguments, true),
                                    contentId: 'c' . $this->menuContentObjectUid,
                                )
                            );
                        }
                    }
                    $groupItem->items[] = $item;
                }

                if ($groupItem->activeItemsUids) {
                    $groupItem->active = true;

                    $closeArguments = $arguments;
                    if (!is_array($closeArguments['demand'])) {
                        $closeArguments['demand'] = [];
                    }
                    $newDemandCategory = implode(',', array_diff($currentCategories, $groupItem->activeItemsUids)) ?: null;
                    if ($newDemandCategory) {
                        $closeArguments['demand'][$this->settings['categoryDemandKey']] = $newDemandCategory;
                    } else {
                        unset($closeArguments['demand'][$this->settings['categoryDemandKey']]);
                    }
                    $groupItem->closeItem = new CategoryFilterItem(
                        label: $groupItem->label,
                        url: $this->buildUri($closeArguments),
                        fetchLinkOptions: new FetchLinkOptions(
                            url: $this->buildUri($closeArguments, true),
                            contentId: 'c' . $this->menuContentObjectUid,
                        )
                    );
                }

                $filter->items[] = $groupItem;
            }
        }

        return $filter;
    }

    protected function buildItem(
        Category $category,
        string $overrideActiveCategories = '',
        string $categoryListInsteadOfUnset = ''
    ): CategoryFilterItem
    {
        $activeCategories = $overrideActiveCategories ?: $this->activeCategories;
        $isActive = $activeCategories && in_array($category->getUid(), explode(',', $activeCategories));
        $newCategoryList = (string)$category->getUid();
        $unsetCategory = false;
        // remove page and object arguments from uri
        $arguments = $this->request->getArguments();
        foreach($this->settings['unsetArguments'] as $unsetArgument) {
            unset($arguments[$unsetArgument]);
        }

        if ($isActive && !$this->settings['multiSelect']) {
            if ($categoryListInsteadOfUnset) {
                $newCategoryList = $categoryListInsteadOfUnset;
            } else {
                $unsetCategory = true;
            }
        }
        if ($this->settings['multiSelect']) {
            if ($isActive) {
                if ($this->activeCategories == $category->getUid() || !$this->activeCategories) {
                    $unsetCategory = true;
                } else {
                    $newCategoryList = implode(',', array_diff(explode(',', $this->activeCategories), [$category->getUid()]));
                }
            } else {
                $newCategoryList = $this->activeCategories ? $this->activeCategories . ',' . $newCategoryList : $newCategoryList;
            }
        }
        if ($unsetCategory) {
            unset($arguments['demand'][$this->settings['categoryDemandKey']]);
        } else {
            $arguments['demand'][$this->settings['categoryDemandKey']] = $newCategoryList;
        }

        if ($this->settings['checkPotential'] && $this->menuRepository && $this->menuDemand) {
            $potentialDemand = $this->menuDemand;
            $potentialDemand->{$this->settings['categoryDemandKey']} = $newCategoryList;
            $potentialDemand->limit = 1;
            $hasNoPotential = !$this->menuRepository->findByMenuDemand($potentialDemand)->getFirst();
        }

        $url = $this->buildUri($arguments);
        return new CategoryFilterItem(
            label: $category->title,
            url: $url,
            fetchLinkOptions: new FetchLinkOptions(
                url: ($this->menuContentObjectUid && $this->fetchLinkPageType) ? $this->buildUri($arguments, true) : $url,
                contentId: 'c' . $this->menuContentObjectUid,
            ),
            active: $isActive,
            hasNoPotential: $hasNoPotential ?? false,
        );
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
        $titleProvider->setTitle($titleProvider->getTitle() . $pageTitleSuffixCategory);
        return $this;
    }

}