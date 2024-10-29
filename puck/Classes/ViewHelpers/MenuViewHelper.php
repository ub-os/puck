<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\DebugUtility;

use B13\Menus\DataProcessing\BreadcrumbsMenu;
use B13\Menus\DataProcessing\LanguageMenu;
use B13\Menus\DataProcessing\ListMenu;
use B13\Menus\DataProcessing\TreeMenu;

class MenuViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('pages', 'string', '', false, '1');
        $this->registerArgument('depth', 'integer', '', false, 1);
        $this->registerArgument('processor', 'string', '', false, 'list');
        $this->registerArgument('excludePages', 'string', '', false, '');
        $this->registerArgument('includeNotInMenu', 'boolean', '', false, null);
        $this->registerArgument('excludeLanguages', 'string', '', false, '');
        $this->registerArgument('addAllSiteLanguages', 'boolean', '', false, false);
        $this->registerArgument('excludeDoktypes', 'string', '', false, '199,254,255');
        $this->registerArgument('processMedia', 'boolean', '', false, false);
        $this->registerArgument('set', 'string', '', false, '');
    }

    public function render(): ?array
    {
        $processor = $this->arguments['processor'];
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $as = 'menu';
        switch ($processor) {
            case 'tree':
                $dataProcessor = GeneralUtility::makeInstance(TreeMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'entryPoints' => $this->arguments['pages'],
                    'depth' => $this->arguments['depth'],
                    'excludePages' => $this->arguments['excludePages'],
                    'includeNotInMenu' => $this->arguments['includeNotInMenu'] ?? false,
                    'excludeDoktypes' => $this->arguments['excludeDoktypes'],
                ];
                break;
            case 'language':
                $dataProcessor = GeneralUtility::makeInstance(LanguageMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'includeNotInMenu' => $this->arguments['includeNotInMenu'] ?? true,
                    'excludeLanguages' => $this->arguments['excludeLanguages'],
                    'addAllSiteLanguages' => $this->arguments['addAllSiteLanguages'],
                ];
                break;
            case 'breadcrumbs':
                $dataProcessor = GeneralUtility::makeInstance(BreadcrumbsMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'excludePages' => $this->arguments['excludePages'],
                    'includeNotInMenu' => $this->arguments['includeNotInMenu'] ?? false,
                    'excludeDoktypes' => $this->arguments['excludeDoktypes'],
                ];
                break;
            default:
                $dataProcessor = GeneralUtility::makeInstance(ListMenu::class);
                $processorConfiguration = [
                    'as' => $as,
                    'pages' => $this->arguments['pages'],
                    'includeNotInMenu' => $this->arguments['includeNotInMenu'] ?? false,
                    'excludeDoktypes' => $this->arguments['excludeDoktypes'],
                ];
        }
        $processorConfiguration = GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($processorConfiguration);
        if ($this->arguments['processMedia']) {
            $processorConfiguration['dataProcessing.'] = [
                '10' => 'TYPO3\CMS\Frontend\DataProcessing\FilesProcessor',
                '10.' => [
                    'references.' => [
                        'table' => 'pages',
                        'fieldName' => 'media',
                    ],
                    'as' => 'processedMedia',
                ],
            ];
        }
        $result = $dataProcessor->process($contentObjectRenderer, [], $processorConfiguration, [])[$as];
        if ($this->arguments['set']) {
            $this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
            return null;
        }
        return $result;
    }
}