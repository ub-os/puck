<?php
namespace UBOS\Puck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use GeorgRinger\NumberedPagination\NumberedPagination;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
/**
 *
 */
class PuckUtility
{
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

    public static function convertZeroStringsToInteger(mixed $value, bool $convertToNull = false): mixed
    {
        if (is_array($value)) {
            $array = [];
            foreach($value as $key => $value) {
                $array[$key] = static::convertZeroStringsToInteger($value, $convertToNull);
            }
            return $array;
        } else if ($value === '0') {
            if ($convertToNull) {
                return null;
            }
            return 0;
        }
        return $value;
    }

}