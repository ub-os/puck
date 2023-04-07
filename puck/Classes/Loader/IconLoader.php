<?php
namespace UBOS\Puck\Loader;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use UBOS\Puck\Utility\PuckUtility;

class IconLoader
{
    public static function loadIconsFromPath(string $path, string $extensionKey = 'puck'): void
    {
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $iconsPath = ExtensionManagementUtility::extPath($extensionKey) . $path;
        $icons = PuckUtility::getBaseFilesInDir($iconsPath, 'svg');
        foreach ($icons as $item) {
            $iconRegistry->registerIcon(
                GeneralUtility::camelCaseToLowerCaseUnderscored($item),
                SvgIconProvider::class,
                ['source' => 'EXT:' . $extensionKey . '/'. $path . $item . '.svg']
            );
        }
    }

    public static function loadBackendIcons(): void
    {
        self::loadIconsFromPath('Resources/Public/Icons/Backend/');
    }

    public static function loadContentIcons(): void
    {
        self::loadIconsFromPath('Resources/Public/Icons/Content/');
    }
}