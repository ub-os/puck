<?php

namespace UBOS\Puck\Menu\Trait\Controller;

use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use GeorgRinger\NumberedPagination\NumberedPagination;
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

    public function createNumberedPaginator(
        QueryResultPaginator $paginator,
        int $maximumLinks = 3
    ): NumberedPagination
    {
        return new NumberedPagination($paginator, $maximumLinks);
    }

    public function buildPagination(
        QueryResultPaginator $paginator,
        NumberedPagination $numberedPaginator,
        string $type = 'pagination',
        ): Pagination
    {
        $arguments = $this->request->getArguments();
        $currentPage = intval($arguments['page'] ?? '1');
        $prevPage = $currentPage > 1
            ? $currentPage - 1
            : 0;
        $nextPage = $currentPage < $paginator->getNumberOfPages()
            ? $currentPage + 1
            : 0;
        $separatorLeft = $numberedPaginator->getDisplayRangeStart() > 2;
        $separatorRight = ($paginator->getNumberOfPages() - $numberedPaginator->getDisplayRangeEnd()) > 1;
        $itemsPages = $paginator->getNumberOfPages() > 2
            ? range(
            $separatorLeft
                ? $numberedPaginator->getDisplayRangeStart()
                : 2,
            $separatorRight
                ? $numberedPaginator->getDisplayRangeEnd()
                : $paginator->getNumberOfPages() - 1
            )
            : [];

        unset($arguments['object']);
        $arguments['page'] = $nextPage;

        return match($type) {
            'load-more', 'infinite-scroll' => new Pagination(
                loadMore: new PaginationItem(
                    label: '+',
                    url: $this->buildPaginationUri($arguments),
                    fetchLinkOptions: new FetchLinkOptions(
                        url: $this->buildPaginationUri($arguments, true),
                        contentId: 'c' . $this->contentObject->getUid() . '-list',
                        mode: 'append',
                        scrollToContent: 0,
                        trigger: $type === 'infinite-scroll' ? 'scrollIntoView' : 'click',
                    ),
                    disabled: !$nextPage
                )
            ),
            default => new Pagination(
                prev: $prevPage ? $this->buildPaginationItem($prevPage, '<') : null,
                next: $nextPage ? $this->buildPaginationItem($nextPage, '>') : null,
                first: $this->buildPaginationItem(1),
                last: $this->buildPaginationItem($paginator->getNumberOfPages()),
                items: array_map(function($page) {
                    return $this->buildPaginationItem($page);
                }, $itemsPages),
                currentPage: $currentPage,
                separatorLeft: $separatorLeft,
                separatorRight: $separatorRight,
            )
        };
    }

    protected function buildPaginationItem(int $page, string $label = ''): ?PaginationItem
    {
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

}