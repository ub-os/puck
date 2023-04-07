<?php
namespace UBOS\Puck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 *
 */
class TcaUtility
{
    public const STANDARD_CROP_RATIOS = [
        'free' => [
            'title' => 'free',
            'value' => 'NaN'
        ],
        '1:1' => [
            'title' => '1:1',
            'value' => 1
        ],
        '2:1' => [
            'title' => '2:1',
            'value' => 2
        ],
        '3:1' => [
            'title' => '3:1',
            'value' => 3
        ],
        '3:2' => [
            'title' => '3:2',
            'value' => 3 / 2
        ],
        '4:1' => [
            'title' => '4:1',
            'value' => 4
        ],
        '4:3' => [
            'title' => '4:3',
            'value' => 4 / 3
        ],
        '5:2' => [
            'title' => '5:2',
            'value' => 5 / 2
        ],
        '5:3' => [
            'title' => '5:3',
            'value' => 5 / 3
        ],
        '5:4' => [
            'title' => '5:4',
            'value' => 5 / 4
        ],
        '16:9' => [
            'title' => '16:9',
            'value' => 16 / 9
        ],
        '16:10' => [
            'title' => '16:10',
            'value' => 16 / 10
        ]
    ];

    /**
     * Return cropVariant array for TCA.
     * If $allowedRatios isn't set, the identifier is used as ratio
     * @param string $identifier
     * @param array|string|null $allowedRatios
     * @return array
     *
     */
    public static function getCropVariant(string $identifier, array|string|null $allowedRatios = null): array
    {
        if ($allowedRatios === 'standard') {
            return [
                'title' => $identifier,
                'allowedAspectRatios' => self::STANDARD_CROP_RATIOS,
            ];
        }
        if (!is_array($allowedRatios)) {
            $allowedRatios = [$identifier];
        }
        $allowedAspectRatios = array_map(
            fn($id) => [
                'title' => $id,
                'value' => ((int)explode(':', $id)[0] ?? 1) / ((int)explode(':', $id)[1] ?? 1)
            ],
            $allowedRatios
        );
        return [
            'title' => $identifier,
            'allowedAspectRatios' => [
                $identifier => $allowedAspectRatios
            ],
        ];
    }

    public static function getCropVariants(array $variants): array
    {
        $cropVariants = [];
        foreach($variants as $variant) {
            if (isset($variant['identifier'])) {
                $cropVariants[$variant['identifier']] = self::getCropVariant($variant['identifier'], $variant['allowedRatios']);
            }
        }
        return $cropVariants;
    }

    public static function getContentShowitemBase(): string
    {
        return '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,';
    }
}