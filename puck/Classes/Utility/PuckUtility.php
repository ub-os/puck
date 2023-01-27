<?php
namespace UBOS\Puck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use HDNET\Autoloader\Utility\FileUtility;
use GeorgRinger\NumberedPagination\NumberedPagination;

/**
 *
 */
class PuckUtility
{
    /**
     * @return array
     */
    public static function indexContentModels(): array
    {
        $extensionKey = 'puck';
        $modelPath = ExtensionManagementUtility::extPath($extensionKey) . 'Classes/Domain/Model/Content/';
        $models = FileUtility::getBaseFilesInDir($modelPath, 'php');
        $index = [];
        foreach($models as $model) {
            $index[] = [
                'name' => $model,
                'fullName' => 'UBOS\\Puck\\Domain\\Model\\Content\\'.$model,
                'typeKey' => $extensionKey.'_'.GeneralUtility::camelCaseToLowerCaseUnderscored($model)
            ];
        }
        return $index;
    }

    /**
     * @param QueryResult $result
     * @param int $currentPage
     * @param int|string $itemsPerPage
     * @param int $maximumLinks
     * @return array
     */
    public static function paginateQueryResult(QueryResult $result, int $currentPage = 1, int|string $itemsPerPage = 12, int $maximumLinks = 3): array
    {
        $paginator = new QueryResultPaginator(
            $result,
            $currentPage,
            (int)$itemsPerPage
        );
        $pagination = new NumberedPagination($paginator, $maximumLinks);
        $prevPage = $currentPage > 1
            ? $currentPage - 1
            : 0;
        $nextPage = $currentPage < $paginator->getNumberOfPages()
            ? $currentPage + 1
            : 0;
        $window = range($pagination->getDisplayRangeStart(), $pagination->getDisplayRangeEnd());
        if (($key = array_search(1, $window)) !== false) {
            unset($window[$key]);
        }
        if (($key = array_search($paginator->getNumberOfPages(), $window)) !== false) {
            unset($window[$key]);
        }
        return [
                'paginator'=>$paginator,
                'current'=>$currentPage,
                'prev'=>$prevPage,
                'next'=>$nextPage,
                'first'=>1,
                'last'=>$paginator->getNumberOfPages(),
                'window'=>$window,
                'slideLeft'=>$pagination->getHasLessPages(),
                'slideRight'=>$pagination->getHasMorePages(),
                'items'=>$paginator->getPaginatedItems(),
        ];
    }
}