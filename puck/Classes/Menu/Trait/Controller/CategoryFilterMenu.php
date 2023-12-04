<?php

namespace UBOS\Puck\Menu\Trait\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use UBOS\Puck\Domain\Model\Category;
use UBOS\Puck\Domain\Repository\CategoryRepository;
use UBOS\Puck\Menu\Dto\MenuDemand;
use UBOS\Puck\Menu\Dto\CategoryFilter;
use UBOS\Puck\Menu\Dto\CategoryFilterItem;
use UBOS\Puck\Menu\Dto\FetchLinkOptions;
use UBOS\Puck\PageTitle\PuckTitleProvider;

trait CategoryFilterMenu
{
    abstract protected function getMenuActionName(): string;
    abstract protected function getMenuContentObjectUid(): int;
    abstract protected function getFetchLinkPageType(): int;
    abstract protected function getMenuRepository();
    abstract protected function getCategoryRepository(): CategoryRepository;
    abstract protected function getMenuDemand(): MenuDemand;
    protected function buildFilterUri(array $arguments, bool $isFetchUri = false): string
    {
        if ($isFetchUri) {
            $arguments['object'] = $this->getMenuContentObjectUid();
        }
        return $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(!$isFetchUri)
            ->setTargetPageType($isFetchUri ? $this->getFetchLinkPageType() : 0)
            ->uriFor($this->getMenuActionName(), $arguments);
    }
    protected function buildCategoryFilter(
        ?string $categoryList,
        ?string $filterCategories,
        ?string $groupCategories,
        ?string $categoryListArgumentName = 'categoryList',
        ?string $categoryListSettingsName = 'list',
        int $groupDepth = 1,
        array $unsetArguments = ['page', 'object'],
        array $order = ['sorting' => QueryInterface::ORDER_ASCENDING],

        bool $buildSecondLevelOfInactiveParent = false
    ): ?CategoryFilter
    {
        $filter = new CategoryFilter(
            groupDepth: $groupDepth,
        );
        $activeUids = $categoryList ? explode(',', $categoryList) : [];
        $filterSettings = $this->settings['categoryFilter'];
        if (!isset($this->settings['categoryFilter'])
            || !$filterSettings['active']
            || (!$filterCategories && !$groupCategories)
        ) {
            return null;
        }

        // remove page and object arguments from uri
        $arguments = $this->request->getArguments();
        foreach($unsetArguments as $unsetArgument) {
            unset($arguments[$unsetArgument]);
        }

        // create category query
        $this->getCategoryRepository()->setDefaultOrderings($order);

        // build items from categories
        if ($filterCategories) {
            $categories = $this->getCategoryRepository()->findByUidList($filterCategories)->toArray();
            foreach($categories as $category) {
                $filter->items[] = $this->buildCategoryFilterItem(
                    $categoryList,
                    $category,
                    $unsetArguments,
                    $categoryListArgumentName,
                    $categoryListSettingsName);
            }
        }

        // build items from parent categories
        if ($groupCategories) {
            $groupFilterCategories = $this->getCategoryRepository()->findByUidList($groupCategories)->toArray();
            foreach($groupFilterCategories as $category) {
                $groupItem = new CategoryFilterItem(
                    label: $category->title,
                );

                $subCategories = $this->getCategoryRepository()->findByParent($category->getUid());
                foreach($subCategories->toArray() as $subCategory) {
                    if (GeneralUtility::inList($categoryList, (string)$subCategory->getUid())) {
                        $groupItem->activeItemsUids[] = $subCategory->getUid();
                    }
                }
                foreach($subCategories->toArray() as $subCategory) {
                    $newActiveUids = $activeUids;
                    if (!($filterSettings['multiSelectWithinGroup'] ?? false) && $filterSettings['multiSelect'] && $groupItem->activeItemsUids) {
                        $newActiveUids = array_diff($activeUids, array_diff($groupItem->activeItemsUids, [$subCategory->getUid()]));
                    }
                    $item = $this->buildCategoryFilterItem(
                        implode(',',$newActiveUids),
                        $subCategory,
                        $unsetArguments,
                        $categoryListArgumentName,
                        $categoryListSettingsName);

                    if ($groupDepth === 2) {
                        $subCategories2 = $this->getCategoryRepository()->findByParent($subCategory->getUid());
                        $item->activeItemsUids[] = $subCategory->getUid();
                        foreach($subCategories2->toArray() as $subCategory2) {
                            if (GeneralUtility::inList($categoryList, (string)$subCategory2->getUid())) {
                                $item->active = true;
                                $item->activeItemsUids[] = $subCategory2->getUid();
                                $groupItem->activeItemsUids[] = $subCategory2->getUid();

                            }
                        }
                        if ($buildSecondLevelOfInactiveParent || $item->active) {
                            foreach($subCategories2->toArray() as $subCategory2) {
                                $item->items[] = $this->buildCategoryFilterItem(
                                    $categoryList,
                                    $subCategory2,
                                    $unsetArguments,
                                    $categoryListArgumentName,
                                    $categoryListSettingsName,
                                    (string)$subCategory->getUid());
                            }
                        }

                        if ($item->active) {
                            $closeArguments = $arguments;
                            $closeArguments[$categoryListArgumentName] = implode(',', array_diff($activeUids, $item->activeItemsUids)) ?: null;
                            $item->closeItem = new CategoryFilterItem(
                                label: $item->label,
                                url: $this->buildFilterUri($closeArguments),
                                fetchLinkOptions: new FetchLinkOptions(
                                    url: $this->buildFilterUri($closeArguments, true),
                                    contentId: 'c' . $this->getMenuContentObjectUid(),
                                )
                            );
                        }
                    }
                    $groupItem->items[] = $item;
                }
                if ($groupItem->activeItemsUids) {
                    $groupItem->active = true;
                    $closeArguments = $arguments;
                    $closeArguments[$categoryListArgumentName] = implode(',', array_diff($activeUids, $groupItem->activeItemsUids)) ?: null;
                    $groupItem->closeItem = new CategoryFilterItem(
                        label: $groupItem->label,
                        url: $this->buildFilterUri($closeArguments),
                        fetchLinkOptions: new FetchLinkOptions(
                            url: $this->buildFilterUri($closeArguments, true),
                            contentId: 'c' . $this->getMenuContentObjectUid(),
                        )
                    );
                }
                $filter->items[] = $groupItem;
            }
        }

        return $filter;
    }

    protected function buildCategoryFilterItem(
        string $categoryList,
        Category $category,
        array $unsetArguments,
        ?string $categoryListArgumentName,
        ?string $categoryListSettingsName,
        ?string $categoryListInsteadOfUnset = null
    ): CategoryFilterItem
    {
        $isActive = $categoryList && in_array($category->getUid(), explode(',', $categoryList));
        $newCategoryList = (string)$category->getUid();
        $unsetCategory = false;
        $filterSettings = $this->settings['categoryFilter'];

        // remove page and object arguments from uri
        $arguments = $this->request->getArguments();
        foreach($unsetArguments as $unsetArgument) {
            unset($arguments[$unsetArgument]);
        }

        if ($isActive && !$filterSettings['multiSelect']) {
            if ($categoryListInsteadOfUnset) {
                $newCategoryList = $categoryListInsteadOfUnset;
            } else {
                $unsetCategory = true;
            }
        }
        if ($filterSettings['multiSelect']) {
            if ($isActive) {
                if ($categoryList == $category->getUid() || !$categoryList) {
                    $unsetCategory = true;
                } else {
                    $newCategoryList = implode(',', array_diff(explode(',', $categoryList), [$category->getUid()]));
                }
            } else {
                $newCategoryList = $categoryList ? $categoryList . ',' . $newCategoryList : $newCategoryList;
            }
        }
        if ($unsetCategory) {
            unset($arguments[$categoryListArgumentName]);
        } else {
            $arguments[$categoryListArgumentName] = $newCategoryList;
        }

        if ($filterSettings['checkPotential']) {
            $potentialDemand = $this->getMenuDemand();
            $potentialDemand->categoryList = $arguments['categoryList'] ?? '';
            $potentialDemand->categoryList2 = $arguments['categoryList2'] ?? '';
            $potentialDemand->{$categoryListSettingsName} = $newCategoryList;
            $potentialDemand->limit = 1;
            $hasNoPotential = !$this->getMenuRepository()->findByMenuDemand($potentialDemand)->getFirst();
        }

        return new CategoryFilterItem(
            label: $category->title,
            url: $this->buildFilterUri($arguments),
            fetchLinkOptions: new FetchLinkOptions(
                url: $this->buildFilterUri($arguments, true),
                contentId: 'c' . $this->getMenuContentObjectUid(),
            ),
            active: $isActive,
            hasNoPotential: $hasNoPotential ?? false,
        );
    }

    protected function addMenuCategorySuffixToPageTitle(?string $categoryList): void
    {
        if (!$categoryList) {
            return;
        }
        $query =  $this->getCategoryRepository()->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $constraints = [
            $query->in('uid', explode(',',$categoryList)),
        ];
        $categories = $query->matching($query->logicalAnd(...$constraints))->execute()->toArray();
        $pageTitleSuffixCategory =
            PuckTitleProvider::TITLE_DIVIDER .
            implode(', ', array_map(function(Category $category) {
                return $category->title;
            }, $categories));
        $titleProvider = GeneralUtility::makeInstance(PuckTitleProvider::class);
        $titleProvider->setTitle($titleProvider->getTitle() . $pageTitleSuffixCategory);
    }

}