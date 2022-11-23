<?php
namespace UBOS\Theme\ViewHelpers\Repository\Page;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use UBOS\Theme\Domain\Repository\PageRepository;

class GetBreadcrumbsMenuViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('uid', 'mixed', '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $pageRepository = $objectManager->get(PageRepository::class);
        return $pageRepository->getBreadcrumbsMenu($arguments['uid']);
    }
}
