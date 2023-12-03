<?php

namespace UBOS\Puck\Menu\Trait\Controller;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use UBOS\Puck\Menu\Dto\Pagination;
use UBOS\Puck\Menu\Dto\PaginationItem;
use UBOS\Puck\Menu\Dto\FetchLinkOptions;

trait PaginationMenu
{
    abstract protected function getMenuActionName(): string;
    abstract protected function getMenuContentObjectUid(): int;
    abstract protected function getFetchLinkPageType(): int;
    protected function buildPaginationUri(array $arguments, bool $isFetchUri = false): string
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

    public function createPaginator(
        QueryResult $result,
        int $itemsPerPage = 12,
    ): QueryResultPaginator
    {
        $arguments = $this->request->getArguments();
        $currentPage = intval($arguments['page'] ?? '1');
        return new QueryResultPaginator(
            $result,
            $currentPage,
            $itemsPerPage
        );
    }

    public function createSlidingWindowPagination(
        QueryResultPaginator $paginator,
        int $maximumLinks = 3
    ): SlidingWindowPagination
    {
        return new SlidingWindowPagination($paginator, $maximumLinks);
    }

    public function buildPagination(
        QueryResultPaginator $paginator,
        SlidingWindowPagination $slidingWindowPagination,
        string $type = 'pagination',
        ): Pagination
    {
        $itemsPages = $slidingWindowPagination->getLastPageNumber() > 2
            ? range(
                $slidingWindowPagination->getHasLessPages()
                ? $slidingWindowPagination->getDisplayRangeStart()
                : 2,
                $slidingWindowPagination->getHasMorePages()
                ? $slidingWindowPagination->getDisplayRangeEnd()
                : $slidingWindowPagination->getLastPageNumber() - 1
            )
            : [];
        $loadMoreArgs = $this->request->getArguments();
        unset($loadMoreArgs['object']);
        $loadMoreArgs['page'] = $slidingWindowPagination->getNextPageNumber();
        return match($type) {
            'load-more', 'infinite-scroll' => new Pagination(
                loadMore: new PaginationItem(
                    label: '+',
                    url: $this->buildPaginationUri($loadMoreArgs),
                    fetchLinkOptions: new FetchLinkOptions(
                        url: $this->buildPaginationUri($loadMoreArgs, true),
                        contentId: 'c' . $this->contentObject->getUid() . '-list',
                        mode: 'append',
                        scrollToContent: 0,
                        trigger: $type === 'infinite-scroll' ? 'scrollIntoView' : 'click',
                    ),
                    disabled: !$slidingWindowPagination->getNextPageNumber()
                )
            ),
            default => new Pagination(
                prev: $this->buildPaginationItem($slidingWindowPagination->getPreviousPageNumber(), '<'),
                next: $this->buildPaginationItem($slidingWindowPagination->getNextPageNumber(), '>'),
                first: $this->buildPaginationItem($slidingWindowPagination->getFirstPageNumber()),
                last: $this->buildPaginationItem($slidingWindowPagination->getLastPageNumber()),
                items: array_map(function($page) {
                    return $this->buildPaginationItem($page);
                }, $itemsPages),
                currentPage: $slidingWindowPagination->getPaginator()->getCurrentPageNumber(),
                separatorLeft: $slidingWindowPagination->getHasLessPages(),
                separatorRight: $slidingWindowPagination->getHasMorePages(),
            )
        };
    }

    protected function buildPaginationItem(?int $page, string $label = ''): ?PaginationItem
    {
        if (!$page) {
            return null;
        }
        $arguments = $this->request->getArguments();
        $active = $page == intval($arguments['page'] ?? '1');
        unset($arguments['object']);
        if ($page === 1) {
            unset($arguments['page']);
        } else {
            $arguments['page'] = $page;
        }
        return new PaginationItem(
            label: $label ?: $page,
            url: $this->buildPaginationUri($arguments),
            fetchLinkOptions: new FetchLinkOptions(
                url: $this->buildPaginationUri($arguments, true),
                contentId: 'c' . $this->getMenuContentObjectUid(),
            ),
            active: $active,
        );
    }

    protected function addPaginationLinksToHead(QueryResultPaginator $paginator,  ?array $arguments): void
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $currentPage = intval($arguments['page'] ?? '1');
        $prevPage = $currentPage > 1
            ? $currentPage - 1
            : 0;
        $nextPage = $currentPage < $paginator->getNumberOfPages()
            ? $currentPage + 1
            : 0;
        if ($prevPage) {
            $arguments['page'] = $prevPage;
            $pageRenderer->addHeaderData('<link rel="prev" href="' . $this->buildPaginationUri($arguments) . '" />');
        }
        if ($nextPage) {
            $arguments['page'] = $nextPage;
            $pageRenderer->addHeaderData('<link rel="next" href="' . $this->buildPaginationUri($arguments) . '" />');
        }
    }
}