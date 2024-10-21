<?php
namespace UBOS\Puck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
/**
 *
 */
class ContentElementUtility
{
    public static function register(
        string $CType,
        string $label,
        string $description,
        string $group = '01_content',
        string $icon = '',
        string $showItem = '',
        array $columnsOverrides = [],
        string $pluginName = '',
        string $extensionName = 'puck',
        string $vendorName = 'UBOS',
        string $model = '',
        array $containerConfiguration = [],
        array $dataProcessing = [],
        string $previewRenderer = ''
    ) {}
}