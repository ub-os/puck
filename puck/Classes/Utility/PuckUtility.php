<?php
namespace UBOS\Puck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use GeorgRinger\NumberedPagination\NumberedPagination;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
/**
 *
 */
class PuckUtility
{

    /**
     * @param QueryResult $result
     * @param int $currentPage
     * @param int $itemsPerPage
     * @param int $maximumLinks
     * @return array
     */
    public static function paginateQueryResult(
        QueryResult $result,
        int $currentPage = 1,
        int $itemsPerPage = 12,
        int $maximumLinks = 3): array
    {
        $paginator = new QueryResultPaginator(
            $result,
            $currentPage,
            $itemsPerPage
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

    /**
     * @param string $filepath
     * @param bool $keysFromFirstRow
     * @param string $delimiter
     * @return array
     */
    public static function getArrayFromFile(string $filepath, bool $keysFromFirstRow = true, string $delimiter = ','): array
    {
        $pathExplodeDot = explode('.', $filepath);
        $fileExt = end($pathExplodeDot);
        $contents = file_get_contents(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . $filepath);
        switch ($fileExt) {
            case 'json':
                $result = json_decode($contents);
                break;
            case 'csv':
                $array = array_map('str_getcsv', explode("\n", $contents));
                if ($keysFromFirstRow) {
                    $headings = array_shift($array);
                    $headings = array_map(function ($heading) {
                        $string = str_replace(' ', '_', $heading);
                        return preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($string));
                    }, $headings);
                    array_walk(
                        $array,
                        function (&$row) use ($headings) {
                            $row = array_combine($headings, $row);
                        }
                    );
                }
                $result = $array;
                break;
            case 'xml':
                $xml = simplexml_load_string($contents, "SimpleXMLElement", LIBXML_NOCDATA);
                $json = json_encode($xml);
                $result = json_decode($json,TRUE);
                break;
            case 'xlsx':
                $reader = new Xlsx();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . $filepath);
                $sheets = [];
                $sheetNames = $spreadsheet->getSheetNames();
                foreach($spreadsheet->getWorksheetIterator() as $index => $worksheet) {
                    $sheet = $worksheet->toArray();
                    if ($keysFromFirstRow) {
                        $headings = array_shift($sheet);
                        $headings = array_map(function ($heading) {
                            $string = str_replace(' ', '_', $heading);
                            return preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($string));
                        }, $headings);
                        array_walk(
                            $sheet,
                            function (&$row) use ($headings) {
                                $row = array_combine($headings, $row);
                            }
                        );
                    }
                    $sheets[$sheetNames[$index]] = $sheet;
                }
                if (count($sheetNames) === 1) {
                    $result = $sheets[$sheetNames[0]];
                } else {
                    $result = $sheets;
                }
                break;
            default:
                if ($delimiter) {
                    $result = explode($delimiter, $contents);
                } else {
                    $result = $contents;
                }
        }
        return $result;
    }

    public static function getBaseFilesInDir(string $dirPath, string $fileExtension): array
    {
        if (!is_dir($dirPath)) {
            return [];
        }
        $files = GeneralUtility::getFilesInDir($dirPath, $fileExtension);
        foreach ($files as $key => $file) {
            $files[$key] = PathUtility::pathinfo($file, PATHINFO_FILENAME);
        }

        return array_values($files);
    }
}