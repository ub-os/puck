<?php
namespace UBOS\Puck\ViewHelpers\Data;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

use B13\Menus\DataProcessing\BreadcrumbMenu;
use B13\Menus\DataProcessing\LanguageMenu;
use B13\Menus\DataProcessing\ListMenu;
use B13\Menus\DataProcessing\TreeMenu;

class MenuViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('pages', 'string', '', false, '1');
        $this->registerArgument('depth', 'integer', '', false, 1);
        $this->registerArgument('processor', 'string', '', false, 'list');
        $this->registerArgument('excludePages', 'string', '', false, '');
        $this->registerArgument('includeNotInMenu', 'boolean', '', false, false);
        $this->registerArgument('excludeLanguages', 'boolean', '', false, '');
        $this->registerArgument('addAllSiteLanguages', 'boolean', '', false, false);
        $this->registerArgument('excludeDoktypes', 'boolean', '', false, '199,254,255');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $processor = $arguments['processor'];
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $as = 'menu';
        switch ($processor) {
            case 'tree':
                $dataProcessor = GeneralUtility::makeInstance(TreeMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'entryPoints' => $arguments['pages'],
                    'depth' => $arguments['depth'],
                    'excludePages' => $arguments['excludePages'],
                    'includeNotInMenu' => $arguments['includeNotInMenu'],
                    'excludeDoktypes' => $arguments['excludeDoktypes'],
                ];
                break;
            case 'language':
                $dataProcessor = GeneralUtility::makeInstance(LanguageMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'includeNotInMenu' => $arguments['includeNotInMenu'],
                    'excludeLanguages' => $arguments['excludeLanguages'],
                    'addAllSiteLanguages' => $arguments['addAllSiteLanguages'],
                ];
                break;
            case 'breadcrumb':
                $dataProcessor = GeneralUtility::makeInstance(BreadcrumbMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'excludePages' => $arguments['excludePages'],
                    'includeNotInMenu' => $arguments['includeNotInMenu'],
                    'excludeDoktypes' => $arguments['excludeDoktypes'],
                ];
                break;
            default:
                $dataProcessor = GeneralUtility::makeInstance(ListMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'pages' => $arguments['pages'],
                    'includeNotInMenu' => $arguments['includeNotInMenu'],
                    'excludeDoktypes' => $arguments['excludeDoktypes'],
                ];
        }
        $processorConfiguration = GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($processorConfiguration);
        return $dataProcessor->process($contentObjectRenderer, [], $processorConfiguration, [])[$as];

    }
}
