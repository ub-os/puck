<?php

namespace UBOS\Puck\Menu;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use UBOS\Puck\Menu\Dto\Pagination;
use UBOS\Puck\Menu\Dto\PaginationItem;
use UBOS\Puck\Menu\Dto\FetchLinkOptions;

class PaginationBuilder
{
    protected array $settings = [
        'pageArgumentKey' => 'page',
        'itemsPerPage' => 12,
        'maximumLinks' => 3,
        'variant' => ''
    ];
    protected ?SlidingWindowPagination $slidingWindowPagination = null;
    public function __construct(
        protected QueryResult $result,
        protected Request $request,
        protected UriBuilder $uriBuilder,
        protected string $menuActionName,
        protected int $menuContentObjectUid = 0,
        protected int $fetchLinkPageType = 0,
    ) {
    }

    public function configure(array $settings): self
    {
        $this->settings = array_merge($this->settings, $settings);
        $this->slidingWindowPagination = null;
        return $this;
    }

    public function getSlidingWindowPagination(): SlidingWindowPagination
    {
        if (!$this->slidingWindowPagination) {
            $paginator = new QueryResultPaginator(
                $this->result,
                intval($this->request->getArguments()[$this->settings['pageArgumentKey']] ?? '1'),
                $this->settings['itemsPerPage']
            );
            $this->slidingWindowPagination = new SlidingWindowPagination($paginator, $this->settings['maximumLinks']);
        }
        return $this->slidingWindowPagination;
    }


    protected function buildUri(array $arguments, bool $isFetchUri = false): string
    {
        if ($isFetchUri) {
            $arguments['object'] = $this->menuContentObjectUid;
        }
        return $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(!$isFetchUri)
            ->setTargetPageType($isFetchUri ? $this->fetchLinkPageType : 0)
            ->setTargetPageUid($this->request->getAttribute('routing')->getPageId())
            ->uriFor($this->menuActionName, $arguments);
    }

    public function build(): Pagination
    {
        $loadMoreArgs = $this->request->getArguments();
        unset($loadMoreArgs['object']);
        $swp = $this->getSlidingWindowPagination();
        $loadMoreArgs[$this->settings['pageArgumentKey']] = $swp->getNextPageNumber();
        return match($this->settings['variant']) {
            'load-more', 'infinite-scroll' => new Pagination(
                loadMore: new PaginationItem(
                    label: '+',
                    url: $this->buildUri($loadMoreArgs),
                    fetchLinkOptions: new FetchLinkOptions(
                        url: $this->buildUri($loadMoreArgs, true),
                        contentId: 'c' . $this->menuContentObjectUid . '-list',
                        mode: 'append',
                        scrollToContent: 0,
                        trigger: $this->settings['variant'] === 'infinite-scroll' ? 'intersect' : 'click',
                    ),
                    disabled: !$swp->getNextPageNumber()
                )
            ),
            default => new Pagination(
                currentPage: $swp->getPaginator()->getCurrentPageNumber(),
                prev: $this->buildItem($swp->getPreviousPageNumber(), '<'),
                next: $this->buildItem($swp->getNextPageNumber(), '>'),
                items: array_map(
                    function($page) { return $this->buildItem($page); },
                    $swp->getAllPageNumbers()
                ),
                separatorLeft: $swp->getHasLessPages(),
                separatorRight: $swp->getHasMorePages(),
                first: $swp->getFirstPageNumber() < $swp->getDisplayRangeStart() ? $this->buildItem($swp->getFirstPageNumber()) : null,
                last: $swp->getLastPageNumber() > $swp->getDisplayRangeEnd() ? $this->buildItem($swp->getLastPageNumber()) : null,
            )
        };
    }

    protected function buildItem(?int $page, string $label = ''): ?PaginationItem
    {
        if (!$page) {
            return null;
        }
        $arguments = $this->request->getArguments();
        $active = $page == intval($arguments[$this->settings['pageArgumentKey']] ?? '1');
        unset($arguments['object']);
        if ($page === 1) {
            unset($arguments[$this->settings['pageArgumentKey']]);
        } else {
            $arguments[$this->settings['pageArgumentKey']] = $page;
        }
        $url = $this->buildUri($arguments);
        return new PaginationItem(
            label: $label ?: $page,
            url: $url,
            fetchLinkOptions: new FetchLinkOptions(
                url: ($this->menuContentObjectUid && $this->fetchLinkPageType) ? $this->buildUri($arguments, true) : $url,
                contentId: 'c' . $this->menuContentObjectUid,
            ),
            active: $active,
        );
    }

    public function getPaginatedItems(): QueryResult
    {
        return $this->getSlidingWindowPagination()->getPaginator()->getPaginatedItems();
    }

    public function addPaginationLinksToHead(): self
    {
        $arguments = $this->request->getArguments();
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        if ($this->getSlidingWindowPagination()->getPreviousPageNumber()) {
            $arguments[$this->settings['pageArgumentKey']] = $this->getSlidingWindowPagination()->getPreviousPageNumber();
            $pageRenderer->addHeaderData('<link rel="prev" href="' . $this->buildUri($arguments) . '" />');
        }
        if ($this->getSlidingWindowPagination()->getNextPageNumber()) {
            $arguments[$this->settings['pageArgumentKey']] = $this->getSlidingWindowPagination()->getNextPageNumber();
            $pageRenderer->addHeaderData('<link rel="next" href="' . $this->buildUri($arguments) . '" />');
        }
        return $this;
    }
}